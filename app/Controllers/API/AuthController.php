<?php

namespace App\Controllers\API;

use App\Models\UserModel;
use App\Entities\User;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class AuthController extends BaseAPIController
{
    // ... register method tetap sama ...

    #[OAT\Post(
        path: "/auth/login",
        tags: ["Auth"],
        summary: "Login dengan NIM dan password",
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ["nim", "password"],
                properties: [
                    new OAT\Property(property: "nim", type: "string"),
                    new OAT\Property(property: "password", type: "string", format: "password")
                ]
            )
        ),
        responses: [
            new OAT\Response(response: 200, description: "Logged in"),
            new OAT\Response(response: 400, description: "Bad Request"),
            new OAT\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function login()
    {
        // Validasi manual - tidak pakai validation rules yang require email
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $nim = $data['nim'] ?? null;
        $password = $data['password'] ?? null;

        // Validasi NIM dan password wajib
        if (empty($nim)) {
            return $this->fail('NIM harus diisi', 400);
        }

        if (empty($password)) {
            return $this->fail('Password harus diisi', 400);
        }

        // Cari user berdasarkan NIM
        $model = new UserModel();
        $user = $model->where('nim', $nim)->first();

        if (!$user || !password_verify($password, (string) $user->password)) {
            return $this->fail('NIM atau password salah', 401);
        }

        $token = service('jwt')->issueToken($user);
        
        return $this->success([
            'token' => $token,
            'user' => $user
        ], 'Logged in');
    }

    // ... logout dan me method tetap sama ...
}

