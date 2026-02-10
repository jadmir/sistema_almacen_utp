<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Section;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Listar todos los productos con filtros opcionales
     */
    public function index(Request $request)
    {
        try {
            // Query optimizada con eager loading selectivo
            $query = Product::conRelaciones();

            // Filtro por sección (solo si tiene valor)
            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            // Filtro por tipo de stock (solo si tiene valor)
            if ($request->filled('stock_type_id')) {
                $query->whereHas('section', function ($q) use ($request) {
                    $q->where('stock_type_id', $request->stock_type_id);
                });
            }

            // Filtro por búsqueda usando scope
            if ($request->filled('search')) {
                $query->buscar($request->search);
            }

            // Filtro por stock bajo usando scope
            if ($request->filled('stock_bajo') && filter_var($request->stock_bajo, FILTER_VALIDATE_BOOLEAN)) {
                $query->stockBajo();
            }

            // Filtro por estado
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            // Obtener número de items por página (por defecto 10, máximo 100)
            $perPage = min($request->input('per_page', 10), 100);

            // Select solo campos necesarios
            $productos = $query->select([
                'id', 'section_id', 'deposito_id', 'codigo', 'nombre', 'descripcion',
                'unidad_medida', 'stock_actual', 'stock_minimo', 'stock_maximo',
                'tiene_vencimiento', 'fecha_vencimiento', 'ubicacion', 'estado',
                'created_at', 'updated_at'
            ])
            ->orderBy('codigo', 'asc')
            ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $productos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo producto
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'section_id' => 'required|exists:sections,id',
                'deposito_id' => 'nullable|exists:depositos,id',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'stock_actual' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'unidad_medida' => 'required|string|max:50',
                'ubicacion' => 'nullable|string|max:100',
                'stock_maximo' => 'nullable|integer|min:0',
                'tiene_vencimiento' => 'nullable|boolean',
                'fecha_vencimiento' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener la sección para generar el código
            $section = Section::findOrFail($request->section_id);

            // Buscar el último producto de esta sección para generar el correlativo
            $lastProduct = Product::where('section_id', $request->section_id)
                ->where('codigo', 'like', $section->codigo . '-%')
                ->orderBy('codigo', 'desc')
                ->first();

            // Generar el nuevo número correlativo
            if ($lastProduct) {
                // Extraer el número del último código (ej: "ASSOF-0089" -> 89)
                $lastNumber = intval(substr($lastProduct->codigo, strlen($section->codigo) + 1));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Generar el código completo (ej: "ASSOF-0001")
            $codigo = $section->codigo . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            // Crear el producto con el código generado
            $data = $request->all();
            $data['codigo'] = $codigo;

            $producto = Product::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Producto creado exitosamente',
                'data' => $producto->load('section.stockType')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un producto específico
     */
    public function show($id)
    {
        try {
            $producto = Product::with([
                'section:id,nombre,codigo,stock_type_id',
                'section.stockType:id,nombre,codigo',
                'movements' => function ($query) {
                    $query->select('id', 'product_id', 'user_id', 'area_id', 'tipo', 'cantidad', 'stock_anterior', 'stock_posterior', 'motivo', 'observaciones', 'fecha_movimiento', 'created_at')
                          ->with(['user:id,nombre,email', 'area:id,nombre,codigo'])
                          ->orderBy('created_at', 'desc')
                          ->limit(20); // Limitar movimientos recientes
                }
            ])
            ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $producto
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Producto no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar un producto
     */
    public function update(Request $request, $id)
    {
        try {
            $producto = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'section_id' => 'sometimes|required|exists:sections,id',
                'deposito_id' => 'nullable|exists:depositos,id',
                'codigo' => 'sometimes|required|string|max:50|unique:products,codigo,' . $id,
                'nombre' => 'sometimes|required|string|max:255',
                'descripcion' => 'nullable|string',
                'stock_minimo' => 'sometimes|required|integer|min:0',
                'unidad_medida' => 'sometimes|required|string|max:50',
                'ubicacion' => 'nullable|string|max:100',
                'estado' => 'sometimes|required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // No permitir actualizar stock_actual directamente
            $data = $request->except(['stock_actual']);
            $producto->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Producto actualizado exitosamente',
                'data' => $producto->load('section.stockType')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un producto (soft delete)
     */
    public function destroy($id)
    {
        try {
            $producto = Product::findOrFail($id);
            $producto->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Producto eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar entrada de stock
     */
    public function registrarEntrada(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cantidad' => 'required|integer|min:1',
                'motivo' => 'required|string|max:255',
                'observaciones' => 'nullable|string',
                'fecha_movimiento' => 'required|date|before_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto = Product::findOrFail($id);

            DB::beginTransaction();

            $stockAnterior = $producto->stock_actual;
            $stockNuevo = $stockAnterior + $request->cantidad;

            // Actualizar stock
            $producto->stock_actual = $stockNuevo;
            $producto->save();

            // Registrar movimiento
            $jwtUser = $request->attributes->get('jwt_user');
            Movement::create([
                'product_id' => $producto->id,
                'user_id' => $jwtUser->user_id,
                'tipo' => 'ENTRADA',
                'cantidad' => $request->cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockNuevo,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
                'fecha_movimiento' => $request->fecha_movimiento,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Entrada de stock registrada exitosamente',
                'data' => [
                    'producto' => $producto->load('section.stockType'),
                    'stock_anterior' => $stockAnterior,
                    'stock_actual' => $stockNuevo,
                    'cantidad_ingresada' => $request->cantidad
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar entrada',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar salida de stock
     */
    public function registrarSalida(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cantidad' => 'required|integer|min:1',
                'motivo' => 'required|string|max:255',
                'area_id' => 'required|exists:areas,id',
                'observaciones' => 'nullable|string',
                'fecha_movimiento' => 'required|date|before_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto = Product::findOrFail($id);

            // Validar que hay suficiente stock
            if ($producto->stock_actual < $request->cantidad) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stock insuficiente',
                    'stock_actual' => $producto->stock_actual,
                    'cantidad_solicitada' => $request->cantidad
                ], 400);
            }

            DB::beginTransaction();

            $stockAnterior = $producto->stock_actual;
            $stockNuevo = $stockAnterior - $request->cantidad;

            // Actualizar stock
            $producto->stock_actual = $stockNuevo;
            $producto->save();

            // Registrar movimiento con área
            $jwtUser = $request->attributes->get('jwt_user');
            Movement::create([
                'product_id' => $producto->id,
                'user_id' => $jwtUser->user_id,
                'area_id' => $request->area_id,
                'tipo' => 'SALIDA',
                'cantidad' => $request->cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockNuevo,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
                'fecha_movimiento' => $request->fecha_movimiento,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Salida de stock registrada exitosamente',
                'data' => [
                    'producto' => $producto->load('section.stockType'),
                    'stock_anterior' => $stockAnterior,
                    'stock_actual' => $stockNuevo,
                    'cantidad_retirada' => $request->cantidad,
                    'alerta_stock_bajo' => $stockNuevo <= $producto->stock_minimo
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar salida',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar ajuste de stock
     */
    public function registrarAjuste(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'stock_nuevo' => 'required|integer|min:0',
                'motivo' => 'required|string|max:255',
                'observaciones' => 'nullable|string',
                'fecha_movimiento' => 'required|date|before_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto = Product::findOrFail($id);

            DB::beginTransaction();

            $stockAnterior = $producto->stock_actual;
            $stockNuevo = $request->stock_nuevo;
            $diferencia = $stockNuevo - $stockAnterior;

            // Actualizar stock
            $producto->stock_actual = $stockNuevo;
            $producto->save();

            // Registrar movimiento
            $jwtUser = $request->attributes->get('jwt_user');
            Movement::create([
                'product_id' => $producto->id,
                'user_id' => $jwtUser->user_id,
                'tipo' => 'AJUSTE',
                'cantidad' => abs($diferencia),
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockNuevo,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
                'fecha_movimiento' => $request->fecha_movimiento,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Ajuste de stock realizado exitosamente',
                'data' => [
                    'producto' => $producto->load('section.stockType'),
                    'stock_anterior' => $stockAnterior,
                    'stock_actual' => $stockNuevo,
                    'diferencia' => $diferencia
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar ajuste',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos con stock bajo
     */
    public function productosStockBajo()
    {
        try {
            $productos = Product::with(['section.stockType'])
                ->whereRaw('stock_actual <= stock_minimo')
                ->where('estado', true)
                ->orderBy('stock_actual', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $productos,
                'total' => $productos->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener productos con stock bajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de movimientos de un producto
     */
    public function historialMovimientos($id)
    {
        try {
            $producto = Product::findOrFail($id);

            $movimientos = Movement::where('product_id', $id)
                ->with(['user:id,nombre,email', 'area:id,nombre,codigo'])
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            return response()->json([
                'status' => 'success',
                'producto' => $producto->only(['id', 'codigo', 'nombre']),
                'data' => $movimientos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener historial',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el siguiente código disponible para una sección
     */
    public function getNextCode($sectionId)
    {
        try {
            $section = Section::findOrFail($sectionId);

            // Buscar el último producto de esta sección
            $lastProduct = Product::where('section_id', $sectionId)
                ->where('codigo', 'like', $section->codigo . '-%')
                ->orderBy('codigo', 'desc')
                ->first();

            // Generar el nuevo número correlativo
            if ($lastProduct) {
                $lastNumber = intval(substr($lastProduct->codigo, strlen($section->codigo) + 1));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            // Generar el código completo
            $nextCode = $section->codigo . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'section_id' => $section->id,
                    'section_code' => $section->codigo,
                    'section_name' => $section->nombre,
                    'next_code' => $nextCode,
                    'next_number' => $newNumber
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar código',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar entradas masivas (múltiples productos)
     */
    public function registrarEntradaMasiva(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'productos' => 'required|array|min:1',
                'productos.*.product_id' => 'required|exists:products,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.motivo' => 'required|string|max:255',
                'productos.*.observaciones' => 'nullable|string',
                'productos.*.fecha_movimiento' => 'required|date|before_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $jwtUser = $request->attributes->get('jwt_user');
            $resultados = [];
            $errores = [];

            foreach ($request->productos as $index => $item) {
                try {
                    $producto = Product::findOrFail($item['product_id']);

                    $stockAnterior = $producto->stock_actual;
                    $stockNuevo = $stockAnterior + $item['cantidad'];

                    // Actualizar stock
                    $producto->stock_actual = $stockNuevo;
                    $producto->save();

                    // Registrar movimiento
                    Movement::create([
                        'product_id' => $producto->id,
                        'user_id' => $jwtUser->user_id,
                        'tipo' => 'ENTRADA',
                        'cantidad' => $item['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_posterior' => $stockNuevo,
                        'motivo' => $item['motivo'],
                        'observaciones' => $item['observaciones'] ?? null,
                        'fecha_movimiento' => $item['fecha_movimiento'],
                    ]);

                    $resultados[] = [
                        'product_id' => $producto->id,
                        'codigo' => $producto->codigo,
                        'nombre' => $producto->nombre,
                        'stock_anterior' => $stockAnterior,
                        'cantidad_ingresada' => $item['cantidad'],
                        'stock_actual' => $stockNuevo,
                        'success' => true
                    ];

                } catch (\Exception $e) {
                    $errores[] = [
                        'index' => $index,
                        'product_id' => $item['product_id'] ?? null,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Si hay errores, revertir todo
            if (count($errores) > 0) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al procesar algunos productos',
                    'errores' => $errores,
                    'procesados' => count($resultados),
                    'fallidos' => count($errores)
                ], 400);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Entradas masivas registradas exitosamente',
                'data' => $resultados,
                'total_procesados' => count($resultados)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar entradas masivas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar salidas masivas (múltiples productos)
     */
    public function registrarSalidaMasiva(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'productos' => 'required|array|min:1',
                'productos.*.product_id' => 'required|exists:products,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.area_id' => 'required|exists:areas,id',
                'productos.*.motivo' => 'required|string|max:255',
                'productos.*.observaciones' => 'nullable|string',
                'productos.*.fecha_movimiento' => 'required|date|before_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validar stock antes de procesar
            $erroresStock = [];
            foreach ($request->productos as $index => $item) {
                $producto = Product::find($item['product_id']);
                if ($producto && $producto->stock_actual < $item['cantidad']) {
                    $erroresStock[] = [
                        'index' => $index,
                        'product_id' => $item['product_id'],
                        'codigo' => $producto->codigo,
                        'nombre' => $producto->nombre,
                        'stock_actual' => $producto->stock_actual,
                        'cantidad_solicitada' => $item['cantidad'],
                        'error' => 'Stock insuficiente'
                    ];
                }
            }

            if (count($erroresStock) > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stock insuficiente en algunos productos',
                    'errores' => $erroresStock
                ], 400);
            }

            DB::beginTransaction();

            $jwtUser = $request->attributes->get('jwt_user');
            $resultados = [];
            $alertasStockBajo = [];

            foreach ($request->productos as $item) {
                $producto = Product::findOrFail($item['product_id']);

                $stockAnterior = $producto->stock_actual;
                $stockNuevo = $stockAnterior - $item['cantidad'];

                // Actualizar stock
                $producto->stock_actual = $stockNuevo;
                $producto->save();

                // Registrar movimiento
                Movement::create([
                    'product_id' => $producto->id,
                    'user_id' => $jwtUser->user_id,
                    'area_id' => $item['area_id'],
                    'tipo' => 'SALIDA',
                    'cantidad' => $item['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockNuevo,
                    'motivo' => $item['motivo'],
                    'observaciones' => $item['observaciones'] ?? null,
                    'fecha_movimiento' => $item['fecha_movimiento'],
                ]);

                $alertaStockBajo = $stockNuevo <= $producto->stock_minimo;

                if ($alertaStockBajo) {
                    $alertasStockBajo[] = [
                        'product_id' => $producto->id,
                        'codigo' => $producto->codigo,
                        'nombre' => $producto->nombre,
                        'stock_actual' => $stockNuevo,
                        'stock_minimo' => $producto->stock_minimo
                    ];
                }

                $resultados[] = [
                    'product_id' => $producto->id,
                    'codigo' => $producto->codigo,
                    'nombre' => $producto->nombre,
                    'stock_anterior' => $stockAnterior,
                    'cantidad_retirada' => $item['cantidad'],
                    'stock_actual' => $stockNuevo,
                    'alerta_stock_bajo' => $alertaStockBajo,
                    'success' => true
                ];
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Salidas masivas registradas exitosamente',
                'data' => $resultados,
                'total_procesados' => count($resultados),
                'alertas_stock_bajo' => $alertasStockBajo
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar salidas masivas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar ajustes masivos (múltiples productos)
     */
    public function registrarAjusteMasivo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'productos' => 'required|array|min:1',
                'productos.*.product_id' => 'required|exists:products,id',
                'productos.*.stock_nuevo' => 'required|integer|min:0',
                'productos.*.motivo' => 'required|string|max:255',
                'productos.*.observaciones' => 'nullable|string',
                'productos.*.fecha_movimiento' => 'required|date|before_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $jwtUser = $request->attributes->get('jwt_user');
            $resultados = [];

            foreach ($request->productos as $item) {
                $producto = Product::findOrFail($item['product_id']);

                $stockAnterior = $producto->stock_actual;
                $stockNuevo = $item['stock_nuevo'];
                $diferencia = $stockNuevo - $stockAnterior;

                // Actualizar stock
                $producto->stock_actual = $stockNuevo;
                $producto->save();

                // Registrar movimiento
                Movement::create([
                    'product_id' => $producto->id,
                    'user_id' => $jwtUser->user_id,
                    'tipo' => 'AJUSTE',
                    'cantidad' => abs($diferencia),
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockNuevo,
                    'motivo' => $item['motivo'],
                    'observaciones' => $item['observaciones'] ?? null,
                    'fecha_movimiento' => $item['fecha_movimiento'],
                ]);

                $resultados[] = [
                    'product_id' => $producto->id,
                    'codigo' => $producto->codigo,
                    'nombre' => $producto->nombre,
                    'stock_anterior' => $stockAnterior,
                    'stock_actual' => $stockNuevo,
                    'diferencia' => $diferencia,
                    'success' => true
                ];
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Ajustes masivos realizados exitosamente',
                'data' => $resultados,
                'total_procesados' => count($resultados)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar ajustes masivos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
