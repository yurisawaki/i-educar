<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API i-Educar",
 *      description="Documentação da API do sistema i-Educar",
 *      @OA\Contact(
 *          email="suporte@seudominio.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Servidor Principal"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Insira o token no formato: Bearer {token}"
 * )
 */
class SwaggerInfo
{
}
