<?php

namespace App;

use OpenApi\Attributes as OAT;

#[OAT\Info(title: 'Stugether API', version: '1.0.0')]
#[OAT\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer', bearerFormat: 'JWT')]
final class OpenApiSpec {}


