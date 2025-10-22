<?php

namespace App\Http\Controllers;
use App\Models\Usuario;
use App\Models\Pedido;

use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function store()
    {

        // Tomar 5 usuarios existentes (los más recientes)
        $targets = Usuario::orderBy('id', 'desc')->limit(5)->get()->reverse()->values();

        $created = [];
        foreach ($targets as $idx => $usuario) {
            $created[] = $usuario->pedidos()->create([
                'producto' => "Producto Eloquent #" . ($idx + 1),
                'cantidad' => rand(1, 10),
                'total' => rand(100, 10000) / 100,
            ]);
        }

        return response()->json(['status' => 'ok', 'pedidos_inserted' => count($created)]);
    }

    // Obtener todos los pedidos con nombre y correo del usuario relacionado
    public function index()
    {
        $pedidos = Pedido::with('usuario:id,nombre,correo')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'producto' => $p->producto,
                    'cantidad' => $p->cantidad,
                    'total' => (float) $p->total,
                    'usuario' => $p->usuario ? [
                        // 'id' => $p->usuario->id,
                        'nombre' => $p->usuario->nombre,
                        'correo' => $p->usuario->correo,
                    ] : null,
                    'created_at' => $p->created_at ? $p->created_at->toDateTimeString() : null,
                ];
            });

        return response()->json(['status' => 'ok', 'pedidos' => $pedidos]);
    }

    // Obtener pedidos cuyo total esté en un rango (por query params; por defecto 100-250)
    public function rango(Request $request)
    {
        $min = (float) $request->query('min', 100);
        $max = (float) $request->query('max', 250);

        $pedidos = Pedido::with('usuario:id,nombre,correo')
            ->whereBetween('total', [$min, $max])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'producto' => $p->producto,
                    'cantidad' => $p->cantidad,
                    'total' => (float) $p->total,
                    'usuario' => $p->usuario ? [
                        'nombre' => $p->usuario->nombre,
                        'correo' => $p->usuario->correo,
                    ] : null,
                    'created_at' => $p->created_at ? $p->created_at->toDateTimeString() : null,
                ];
            });

        return response()->json([
            'status' => 'ok',
            'min' => $min,
            'max' => $max,
            'count' => $pedidos->count(),
            'pedidos' => $pedidos,
        ]);
    }

    // Obtener todos los pedidos con datos del usuario, ordenados por total DESC
    public function pedidosOrdenados()
    {
        $pedidos = Pedido::with('usuario:id,nombre,correo')
            ->orderByDesc('total')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'producto' => $p->producto,
                    'cantidad' => $p->cantidad,
                    'total' => (float) $p->total,
                    'usuario' => $p->usuario ? [
                        'id' => $p->usuario->id,
                        'nombre' => $p->usuario->nombre,
                        'correo' => $p->usuario->correo,
                    ] : null,
                    'created_at' => $p->created_at ? $p->created_at->toDateTimeString() : null,
                ];
            });

        return response()->json(['status' => 'ok', 'count' => $pedidos->count(), 'pedidos' => $pedidos]);
    }

    // Obtener la suma total del campo "total" en la tabla pedidos
    public function totalSuma()
    {
        $sum = (float) Pedido::sum('total');

        return response()->json([
            'status' => 'ok',
            'total_sum' => round($sum, 2),
        ]);
    }

    // Obtener el pedido más económico con datos del usuario
    public function pedidoMasEconomico()
{
    $p = Pedido::with('usuario:id,nombre,correo')
        ->orderBy('total', 'asc')
        ->first();

    if (! $p) {
        return response()->json(['error' => 'No hay pedidos'], 404);
    }

    return response()->json([
        'status' => 'ok',
        'pedido' => [
            'id' => $p->id,
            'producto' => $p->producto,
            'cantidad' => $p->cantidad,
            'total' => (float) $p->total,
            'usuario' => $p->usuario ? [
                'nombre' => $p->usuario->nombre,
            ] : null,
            'created_at' => $p->created_at ? $p->created_at->toDateTimeString() : null,
        ],
    ]);
}

    // Producto, cantidad y total de cada pedido agrupado por usuario
    public function agrupadoPorUsuario()
    {
        $usuarios = Usuario::with(['pedidos' => function ($q) {
            $q->select('id', 'usuario_id', 'producto', 'cantidad', 'total')
              ->orderByDesc('id');
        }])->get(['id', 'nombre', 'correo']);

        $result = $usuarios->map(function ($u) {
            return [
                'usuario_id' => $u->id,
                'nombre' => $u->nombre,
                'correo' => $u->correo,
                'pedidos' => $u->pedidos->map(function ($p) {
                    return [
                        'producto' => $p->producto,
                        'cantidad' => (int) $p->cantidad,
                        'total' => (float) $p->total,
                    ];
                })->values(),
            ];
        })->filter(function ($u) {
            return $u['pedidos']->count() > 0;
        })->values();

        return response()->json([
            'status' => 'ok',
            'usuarios_count' => $result->count(),
            'data' => $result,
        ]);
    }

}
