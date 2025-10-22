<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    // Inserta 5 usuarios usando Eloquent
    public function store()
    {
        $created = [];
        for ($i = 1; $i <= 5; $i++) {
            $created[] = Usuario::create([
                'nombre' => "Eloquent Usuario $i",
                'correo' => "eloquent{$i}@example.test",
                'telefono' => str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            ]);
        }

        return response()->json(['status' => 'ok', 'inserted' => count($created)]);
    }

    // Obtener todos los pedidos asociados a un usuario por id
    public function pedidos($id)
    {
        $usuario = Usuario::with('pedidos')->find($id);

        if (! $usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'usuario_id' => $usuario->id,
            'pedidos' => $usuario->pedidos,
        ]);
    }

    // Obtener usuarios cuyo nombre comienza con una letra
    public function buscarPorInicial(Request $request, $letra)
    {
        // permitir pasar ?inicial=X o usar el segmento {letra}
        $letra = $request->query('inicial', $letra);
        $letra = mb_substr($letra, 0, 1);

        $usuarios = Usuario::where('nombre', 'like', $letra . '%')
            ->get(['id', 'nombre', 'correo', 'telefono']);

        return response()->json([
            'status' => 'ok',
            'inicial' => $letra,
            'count' => $usuarios->count(),
            'usuarios' => $usuarios,
        ]);
    }

    // Retorna el total de pedidos para un usuario dado
    public function pedidosCount($id = 5)
    {
        $usuario = Usuario::find($id);

        if (! $usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $count = $usuario->pedidos()->count();

        return response()->json([
            'status' => 'ok',
            'usuario_id' => $usuario->id,
            'pedidos_count' => $count,
        ]);
    }
}
