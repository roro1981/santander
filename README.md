# Servicio de Integración Santander
![Minimum PHP Version](https://shields.io/badge/PHP-8.1.0-blue) ![Minimum Mysql Version](https://shields.io/badge/mysql-8.0-blue) ![Minimum Laravel Version](https://shields.io/badge/laravel-10.0.0-blue)

El Servicio de Integración Santander permite realizar pagos desde Flow utilizando Banco Santander como medio de pago. Este servicio contiene los procesos necesarios para integrarse con Santander, quien provee las APIs para su funcionamiento. De esta forma el servicio puede crear y autorizar órdenes enviadas hacia Santander.

**OpenAPI Spec**: [OpenAPI.yaml](/openapi.yaml)
**Health check endpoint**: /api/v1/health

## Capabilities

-  **[Crear orden](https://gitlab.flowdevelopers.cl/core/integrations/yape/-/wikis/Crear-Orden)** 


## Services
| Name |Url|Http Return Code|
|------|---|----------------|
| [Crear orden](https://gitlab.flowdevelopers.cl/core/integrations/yape/-/wikis/Crear-Orden) | **POST** /api/v1/order/create | 200 OK  <br> 400 Bad Request   <br>500 Internal Server Error

## Integrations

**Url base QA:** https://apisandbox.vnforappstest.com

**Url base PROD:** https://apiprod.vnforapps.com

| Name |Url|Http Return Code|
|------|---|----------------|
| API de Seguridad | /api.security/v1/security | 201 Created <br> 401 Unauthorized <br> 500 Internal Server Error |
| API Yape | /api.yape/v2/yape/transaction/{merchantId} | 200 OK <br> 400 Bad Request <br> 401 Unauthorized <br> 406 Not Acceptable <br> 500 Internal Server Error |
| API de Autorización | /api.authorization/v3/authorization/ecommerce/{merchantId} | 200 OK <br> 400 Bad Request <br> 401 Unauthorized <br> 406 Not Acceptable <br> 500 Internal Server Error |
| API de Reversa | /api.authorization/v3/reverse/ecommerce/{merchantId} | 200 OK <br> 400 Bad Request <br> 401 Unauthorized <br> 500 Internal Server Error |

## Tasks

| Functionality |Description |
|---------------|------------|
| [Autorización de orden](https://gitlab.flowdevelopers.cl/core/integrations/yape/-/wikis/Autorizaci%C3%B3n-de-Orden) | Una vez procesada una request para crear orden desde el Core y obtenido un token Yape, se procede a la autorización para finalizar la orden. |
| [Envío a Core Flow](https://gitlab.flowdevelopers.cl/core/integrations/yape/-/wikis/Env%C3%ADo-a-Core-Flow) | Notificar al Core de Flow una vez que la orden ha sido autorizada |

## Dependencies

Este servicio requiere los siguientes componentes instalados para su funcionamiento:

**composer.json:**
```
"require": {
    "php": "^8.1",
    "guzzlehttp/guzzle": "^7.2",
    "laravel/framework": "^10.10",
    "laravel/sanctum": "^3.3",
    "laravel/tinker": "^2.8",
    "mateusjunges/laravel-kafka": "^1.13"
},
```

## Niubiz Requirements

Niubiz provee credenciales y datos de prueba para poder probar en ambiente de desarrollo y QA.

Para producción es necesario estar afiliado a Niubiz y contar con un user/pass asociado a Flow, además de un identificador de comercio.

## buildspec.yaml

Para el despliegue a ambientes de QA/Stage/Prod se utiliza [AWS Codepipeline](https://aws.amazon.com/es/codepipeline/)

La separación de parámetros o secretos por ambiente se hace utilizando prefijos.

### Parameter Store

Los parámetros tienen como prefijo QA/Staging/Prod, dependiendo del ambiente. 

La aplicación utiliza los siguientes parámetros.

| Nombre | Description |
|-----|-------------|
|/SharedEKS/ECRRepository| Url de repositorio ECR|
|/SharedEKS/EKSRole| Rol de EKS con que se debe conectar el codebuild a cluster|
|/SharedEKS/EKSClusterNamespace| Namespace de k8s donde se desplegará la aplicación|
|/SharedEKS/EKSClusterName| Nombre del cluster k8s|
|/SharedEKS/EKSNodeGroup| Nombre del node-group donde se desplegarán los pods|
|/SharedEKS/EKSRegion|Región donde están los recursos|
|/SharedEKS/CertificateArn|Certificado que utilizará el servicio|
|/SharedEKS/HostedZoneId|Id de hosted zone|
|/SharedEKS/ELBHostedZoneId|Id de balanceador de carga|
|/SharedEKS/Domain|Dominio en el que se desplegará el servicio|

### Secret Manager

Se debe contar con un secreto que tenga por nombre **/ENVIRONMENT/yape**, donde ENVIRONMENT corresponde al nombre del ambiente (qa/staging o prod)

Este secreto debe contar con los siguientes keys:

| Key | Description |
|-----|-------------|
|ECR_APPNAME|Nombre del repositorio ECR|
|SECRET_APP_ENV|Nombre del ambiente|
|SECRET_LOG_CHANNEL|Canal del log. Utilizar stderr|
|SECRET_LOG_LEVEL|Nivel de log. Utilizar debug|
|SECRET_DB_HOST| Host de base de datos|
|SECRET_DB_PORT| Puerto de base de datos|
|SECRET_DB_DATABASE| Nombre de base de datos|
|SECRET_DB_USERNAME| Usuario de base de datos|
|SECRET_DB_PASSWORD| Contraseña de usuario de base de datos|
|SECRET_KAFKA_PORT|Puerto de kafka|
|SECRET_KAFKA_BROKERS|Brokers de kafka|
|SECRET_KAFKA_DEBUG| Indica si se habilita el debug de kafka. Utilizar false|
|SECRET_KAFKA_SASL_USERNAME| Usuario para conectarse a kafka|
|SECRET_KAFKA_SASL_PASSWORD| Contraseña de usuario para conectarse a kafka|
|NR_LICENSE_KEY| Licencia de newrelic. Se debe dejar vacío en ambientes inferiores a "prod"|


