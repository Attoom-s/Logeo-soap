# Motor de búsqueda preparado para SoapUI

Este proyecto implementa un servicio SOAP académico con una operación `buscar`. SoapUI funciona como servidor mediante un MockService y otra computadora puede consumirlo como cliente.

Cada petición puede combinar texto, categoría y fecha. El script Groovy filtra los registros de `datos.json`, guarda la consulta en `consultas.json` y elimina las consultas cuya fecha de expiración ya pasó. Los datos originales nunca se eliminan.

## Archivos

- `bus-busqueda.wsdl`: contrato SOAP.
- `datos.json`: registros consultables.
- `consultas.json`: historial temporal.
- `scripts/dispatch-busqueda.groovy`: lógica dinámica y expiración.
- `requests/buscar-request.xml`: petición de ejemplo para la segunda PC.
- `response/RespuestaBusqueda.xml`: contenido de la respuesta dinámica.
- `BusBusqueda-soapui-project.xml`: plantilla importable del proyecto.

## Opción rápida

1. Conserva todos los archivos en la misma carpeta.
2. En SoapUI selecciona **File > Import Project**.
3. Abre `BusBusqueda-soapui-project.xml`.
4. Abre `BusBusquedaMockService` y presiona el botón verde para iniciarlo.
5. Verifica que use el puerto `8088` y la ruta `/bus-busqueda`.

Si tu versión de SoapUI no conserva la referencia relativa al WSDL, usa la configuración manual siguiente.

## Configuración manual segura

1. Selecciona **File > New SOAP Project**.
2. Escribe `BusBusqueda` como nombre.
3. En **Initial WSDL**, selecciona `bus-busqueda.wsdl`.
4. Activa **Create Requests** y confirma.
5. Haz clic derecho en `BusBusquedaBinding` y selecciona **Generate SOAP MockService**.
6. Configura puerto `8088` y ruta `/bus-busqueda`.
7. Abre la operación `buscar` y crea o renombra una respuesta como `RespuestaBusqueda`.
8. En el contenido de esa respuesta pega exactamente `${=requestContext.responseXml}`.
9. En la operación `buscar`, cambia **Dispatch Style** a **SCRIPT**.
10. Copia todo el contenido de `scripts/dispatch-busqueda.groovy` en el editor del Dispatch Script.
11. Inicia el MockService.

## Prueba desde la PC 1

Abre `Request 1`, usa este endpoint y envía la petición:

```text
http://localhost:8088/bus-busqueda
```

Puedes copiar el contenido de `requests/buscar-request.xml`.

## Consumo desde la PC 2

1. Ambas computadoras deben estar en la misma red.
2. En la PC 1 ejecuta `ipconfig` y copia su IPv4.
3. Inicia el MockService en la PC 1.
4. En la PC 2 crea un proyecto desde el mismo WSDL.
5. Cambia el endpoint de `Request 1` por `http://IP_PC1:8088/bus-busqueda`.
6. Envía la petición.

Ejemplo:

```text
http://192.168.1.15:8088/bus-busqueda
```

Si Windows bloquea el acceso, abre el puerto 8088 en el Firewall de la PC 1.

## Demostración de expiración

Normalmente cada consulta expira siete días después de crearse. Para demostrar la limpieza sin esperar una semana, abre `consultas.json`, cambia `expiraEn` por una fecha pasada y realiza otra búsqueda. El script eliminará la consulta vencida antes de guardar la nueva.
