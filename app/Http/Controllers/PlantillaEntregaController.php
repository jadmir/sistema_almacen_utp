<?php

namespace App\Http\Controllers;

use App\Models\PlantillaEntrega;
use App\Models\PlantillaEntregaDetalle;
use App\Models\Product;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlantillaEntregaController extends Controller
{
    /**
     * Listar todas las plantillas
     */
    public function index(Request $request)
    {
        try {
            $query = PlantillaEntrega::with(['area:id,nombre,codigo', 'creador:id,nombre', 'detalles.producto:id,codigo,nombre']);

            // Filtrar por estado activo/inactivo
            if ($request->filled('activa')) {
                $query->where('activa', filter_var($request->activa, FILTER_VALIDATE_BOOLEAN));
            }

            // Filtrar por área
            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            $plantillas = $query->orderBy('nombre', 'asc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $plantillas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener plantillas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva plantilla
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'area_id' => 'required|exists:areas,id',
                'motivo' => 'required|string|max:255',
                'productos' => 'required|array|min:1',
                'productos.*.product_id' => 'required|exists:products,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.observaciones' => 'nullable|string',
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

            // Crear plantilla
            $plantilla = PlantillaEntrega::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'area_id' => $request->area_id,
                'motivo' => $request->motivo,
                'activa' => true,
                'created_by' => $jwtUser->user_id,
            ]);

            // Crear detalles
            foreach ($request->productos as $producto) {
                PlantillaEntregaDetalle::create([
                    'plantilla_id' => $plantilla->id,
                    'product_id' => $producto['product_id'],
                    'cantidad' => $producto['cantidad'],
                    'observaciones' => $producto['observaciones'] ?? null,
                ]);
            }

            DB::commit();

            $plantilla->load(['area', 'detalles.producto']);

            return response()->json([
                'status' => 'success',
                'message' => 'Plantilla creada exitosamente',
                'data' => $plantilla
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear plantilla',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una plantilla específica
     */
    public function show($id)
    {
        try {
            $plantilla = PlantillaEntrega::with(['area', 'creador:id,nombre', 'detalles.producto'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $plantilla
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plantilla no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar una plantilla
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'descripcion' => 'nullable|string',
                'area_id' => 'sometimes|required|exists:areas,id',
                'motivo' => 'sometimes|required|string|max:255',
                'activa' => 'sometimes|boolean',
                'productos' => 'sometimes|required|array|min:1',
                'productos.*.product_id' => 'required|exists:products,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.observaciones' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $plantilla = PlantillaEntrega::findOrFail($id);

            DB::beginTransaction();

            // Actualizar plantilla
            $plantilla->update($request->only(['nombre', 'descripcion', 'area_id', 'motivo', 'activa']));

            // Si se envían productos, actualizar detalles
            if ($request->has('productos')) {
                // Eliminar detalles existentes
                PlantillaEntregaDetalle::where('plantilla_id', $plantilla->id)->delete();

                // Crear nuevos detalles
                foreach ($request->productos as $producto) {
                    PlantillaEntregaDetalle::create([
                        'plantilla_id' => $plantilla->id,
                        'product_id' => $producto['product_id'],
                        'cantidad' => $producto['cantidad'],
                        'observaciones' => $producto['observaciones'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $plantilla->load(['area', 'detalles.producto']);

            return response()->json([
                'status' => 'success',
                'message' => 'Plantilla actualizada exitosamente',
                'data' => $plantilla
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar plantilla',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una plantilla
     */
    public function destroy($id)
    {
        try {
            $plantilla = PlantillaEntrega::findOrFail($id);
            $plantilla->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Plantilla eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar plantilla',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejecutar una plantilla (realizar la salida de productos)
     */
    public function ejecutar(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_movimiento' => 'required|date|before_or_equal:today',
                'observaciones_generales' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $plantilla = PlantillaEntrega::with(['detalles.producto', 'area'])->findOrFail($id);

            if (!$plantilla->activa) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La plantilla está inactiva'
                ], 400);
            }

            // Validar stock antes de procesar
            $erroresStock = [];
            foreach ($plantilla->detalles as $detalle) {
                $producto = $detalle->producto;
                if ($producto->stock_actual < $detalle->cantidad) {
                    $erroresStock[] = [
                        'product_id' => $producto->id,
                        'codigo' => $producto->codigo,
                        'nombre' => $producto->nombre,
                        'stock_actual' => $producto->stock_actual,
                        'cantidad_solicitada' => $detalle->cantidad,
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

            foreach ($plantilla->detalles as $detalle) {
                $producto = Product::findOrFail($detalle->product_id);
                
                $stockAnterior = $producto->stock_actual;
                $stockNuevo = $stockAnterior - $detalle->cantidad;

                // Actualizar stock
                $producto->stock_actual = $stockNuevo;
                $producto->save();

                // Observaciones combinadas
                $observaciones = $detalle->observaciones;
                if ($request->observaciones_generales) {
                    $observaciones = $observaciones 
                        ? $observaciones . ' | ' . $request->observaciones_generales
                        : $request->observaciones_generales;
                }

                // Registrar movimiento
                Movement::create([
                    'product_id' => $producto->id,
                    'user_id' => $jwtUser->user_id,
                    'area_id' => $plantilla->area_id,
                    'tipo' => 'SALIDA',
                    'cantidad' => $detalle->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockNuevo,
                    'motivo' => $plantilla->motivo . ' [Plantilla: ' . $plantilla->nombre . ']',
                    'observaciones' => $observaciones,
                    'fecha_movimiento' => $request->fecha_movimiento,
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
                    'cantidad_retirada' => $detalle->cantidad,
                    'stock_actual' => $stockNuevo,
                    'alerta_stock_bajo' => $alertaStockBajo,
                    'success' => true
                ];
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Plantilla ejecutada exitosamente',
                'plantilla' => [
                    'id' => $plantilla->id,
                    'nombre' => $plantilla->nombre,
                    'area' => $plantilla->area->nombre,
                ],
                'data' => $resultados,
                'total_procesados' => count($resultados),
                'alertas_stock_bajo' => $alertasStockBajo
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al ejecutar plantilla',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
