<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Address\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AddressController extends Controller
{
    /**
     * Lista las direcciones del usuario autenticado (la predeterminada primero).
     */
    #[OA\Get(
        path: '/addresses',
        summary: 'Listar mis direcciones',
        tags: ['Direcciones'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Listado de direcciones')],
    )]
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return AddressResource::collection($addresses);
    }

    /**
     * Crea una nueva dirección para el usuario autenticado.
     */
    #[OA\Post(
        path: '/addresses',
        summary: 'Crear una dirección',
        tags: ['Direcciones'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 201, description: 'Dirección creada')],
    )]
    public function store(AddressRequest $request)
    {
        $address = $this->guardar($request, $request->user()->addresses()->make());

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    /**
     * Actualiza una dirección del usuario autenticado.
     */
    #[OA\Put(
        path: '/addresses/{address}',
        summary: 'Actualizar una dirección',
        tags: ['Direcciones'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'address', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Dirección actualizada')],
    )]
    public function update(AddressRequest $request, int $address)
    {
        $direccion = $request->user()->addresses()->findOrFail($address);

        return new AddressResource($this->guardar($request, $direccion));
    }

    /**
     * Elimina una dirección del usuario autenticado.
     */
    #[OA\Delete(
        path: '/addresses/{address}',
        summary: 'Eliminar una dirección',
        tags: ['Direcciones'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'address', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Dirección eliminada')],
    )]
    public function destroy(Request $request, int $address)
    {
        $request->user()->addresses()->findOrFail($address)->delete();

        return response()->noContent();
    }

    private function guardar(AddressRequest $request, Address $address): Address
    {
        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->fill($data);
        $address->save();

        return $address;
    }
}
