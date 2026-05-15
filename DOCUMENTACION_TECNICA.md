# Documentación Técnica SIGEC (Sistema de Gestión de Indicadores)

Este documento centraliza la información crítica para el mantenimiento, despliegue y sincronización del proyecto SIGEC, asegurando que las transiciones entre entornos (Local XAMPP vs. Producción Docker/Dokploy) sean exitosas y sin errores de activos o permisos.

---

## 1. Arquitectura y Entorno

### Tecnologías Core
- **Framework**: PHP 8.2 con Laminas MVC (anteriormente Zend Framework).
- **Servidor Web**: Apache (configurado mediante `Dockerfile`).
- **Base de Datos**: SQL Server (Acceso vía PDO/sqlsrv).
- **Despliegue**: Dokploy (CI/CD automático mediante GitHub).

### Estructura de Directorios Crítica
- `/config/autoload/`: Configuraciones globales y locales (BD, Sesiones).
- `/module/`: Lógica de negocio (Controladores, Modelos, DAOs, Vistas).
- `/public/`: Activos públicos (CSS, JS, Imágenes). **DocumentRoot** en producción.
- `/var/log/sigec/`: Carpeta de logs en producción (Docker).

---

## 2. Configuraciones Críticas de Entorno

### 2.1 Rutas Base (Assets CSS/JS)
Uno de los puntos más críticos al migrar desde XAMPP es la ruta base. 
- **Archivo**: `module/Layout/config/module.config.php`
- **Producción (Correcto)**:
  ```php
  'base_path' => '/',
  'base_url' => '/'
  ```
- **Error Común (XAMPP)**: Si estas variables contienen `/sigec/public/`, los estilos y scripts fallarán en producción con errores 404.

### 2.2 Rutas de Logs
En Linux/Docker, el sistema escribe logs en `/var/log/sigec/`. En Windows/XAMPP, suele usarse `C:/LOGS/`.
- **Acción**: Al sincronizar controladores, asegurar que la variable `$this->rutaLog` apunte a `/var/log/sigec/`.

### 2.3 Conexión a Base de Datos
- **Archivo**: `config/autoload/global.php`
- Se utilizan variables de entorno (`DB_HOST`, `DB_NAME`, etc.) para evitar credenciales hardcodeadas. 
- Dokploy gestiona estas variables en su panel de control.

---

## 3. Flujo de Sincronización (Local a Producción)

Cuando se realicen cambios en un entorno local sin Git (como XAMPP) y se quieran integrar al proyecto principal:

1.  **Copiar carpetas**: Reemplazar `module/` y `public/` con la nueva versión.
2.  **Verificar Rutas Base**: Abrir `module/Layout/config/module.config.php` y asegurar que `base_path` sea `/`.
3.  **Corregir Logs**: Ejecutar un reemplazo masivo de `C:/LOGS/` por `/var/log/sigec/` en todos los archivos PHP.
4.  **Actualizar Autoload**: Si se agregaron nuevos módulos o clases:
    - Registrar el módulo en `config/modules.config.php`.
    - Registrar el namespace en `composer.json` (sección `psr-4`).
    - Ejecutar `composer dump-autoload`.
5.  **Permisos Docker**: Asegurar que el `Dockerfile` tenga la instrucción de permisos para el usuario `www-data`:
    ```dockerfile
    RUN chown -R www-data:www-data /var/www /var/log/sigec
    ```

---

## 4. Solución de Problemas Comunes (Troubleshooting)

| Problema | Causa Probable | Solución |
| :--- | :--- | :--- |
| **CSS/JS no carga (Texto plano)** | `base_path` incorrecto en Layout config. | Cambiar a `'base_path' => '/'`. |
| **Error 500 al autenticar** | Fallo al escribir logs o conexión BD. | Verificar permisos de `/var/log/sigec/` y variables de entorno. |
| **Imágenes rotas** | Ruta incorrecta o permisos de archivo. | Verificar `$this->basePath()` en la vista y permisos del archivo. |
| **Clase no encontrada** | Autoload desactualizado. | Ejecutar `composer dump-autoload` y verificar `composer.json`. |
| **Módulo no carga** | No habilitado en config. | Agregar el nombre del módulo en `config/modules.config.php`. |

---

## 5. Notas de Mantenimiento Dokploy
- Cada `git push` a `main` activa un despliegue.
- Para verificar errores en tiempo real, usar los logs del contenedor en el panel de Dokploy.
- Si se añaden dependencias de PHP, actualizar las extensiones en el `Dockerfile` (ej. `libpng-dev`, `libzip-dev`).

---
**Última actualización:** 15 de Mayo de 2026.
