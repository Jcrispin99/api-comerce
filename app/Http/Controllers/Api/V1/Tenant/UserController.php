<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @group Usuarios (Tenant)
 * @authenticated
 *
 * Gestión de usuarios dentro del tenant. Permite al administrador de la tienda crear y gestionar sus propios usuarios.
 */
final class UserController extends ApiController
{
    /**
     * Listar usuarios.
     *
     * Obtiene todos los usuarios registrados en el tenant actual.
     */
    public function index(): JsonResponse
    {
        return $this->success(User::with('company')->get());
    }

    /**
     * Crear usuario.
     *
     * Registra un nuevo usuario dentro del tenant.
     */
    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return $this->created($user, 'Usuario creado exitosamente.');
    }

    /**
     * Mostrar usuario.
     *
     * Obtiene los detalles de un usuario específico.
     */
    public function show(User $user): JsonResponse
    {
        return $this->success($user->load('company'));
    }

    /**
     * Actualizar usuario.
     *
     * Actualiza la información de un usuario existente.
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $this->success($user, 'Usuario actualizado exitosamente.');
    }

    /**
     * Eliminar usuario.
     *
     * Elimina un usuario del sistema.
     */
    public function destroy(User $user): JsonResponse
    {
        // Evitar que un usuario se elimine a sí mismo
        if (Auth::id() === $user->id) {
            return $this->error('No puedes eliminar tu propio usuario.', 403);
        }

        $user->delete();

        return $this->success(message: 'Usuario eliminado exitosamente.');
    }
}
