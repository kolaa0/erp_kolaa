<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Connexion</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <style>
        body { background: linear-gradient(135deg, var(--bl) 0%, var(--dark) 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { background: white; border-radius: 12px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .login-header { text-align: center; margin-bottom: 28px; }
        .login-logo { width: 50px; height: 50px; background: var(--or); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; color: white; margin: 0 auto 14px; }
        .login-title { font-size: 18px; font-weight: 700; color: var(--dark); margin-bottom: 4px; }
        .login-sub { font-size: 12px; color: var(--muted); }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--mid); margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 9px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; transition: border-color 0.2s; }
        .form-group input:focus { outline: none; border-color: var(--bl2); box-shadow: 0 0 0 3px rgba(26, 95, 168, 0.1); }
        .btn-login { width: 100%; padding: 10px; background: var(--bl); color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
        .btn-login:hover { background: var(--bl2); }
        .btn-login:disabled { background: var(--muted); cursor: not-allowed; }
        .form-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 12px; }
        .form-footer a { color: var(--bl2); text-decoration: none; }
        .error-msg { background: #fee2e2; color: #991b1b; padding: 8px 12px; border-radius: 5px; margin-bottom: 14px; font-size: 12px; display: none; }
        .success-msg { background: #d1fae5; color: #065f46; padding: 8px 12px; border-radius: 5px; margin-bottom: 14px; font-size: 12px; display: none; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <div class="login-logo">K</div>
            <div class="login-title">AgencePro ERP</div>
            <div class="login-sub">Connexion à votre espace</div>
        </div>

        <div class="error-msg" id="error-msg"></div>
        <div class="success-msg" id="success-msg"></div>

        <form id="login-form">
            <div class="form-group">
                <label>Email ou identifiant</label>
                <input type="email" name="email" value="admin@kola.com" required>
            </div>

            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" value="password" required>
            </div>

            <button type="submit" class="btn-login" id="btn-submit">
                <i class="ti ti-login"></i> Connexion
            </button>

            <div class="form-footer">
                <label style="display:flex;align-items:center;gap:4px;cursor:pointer">
                    <input type="checkbox" name="remember"> Se souvenir de moi
                </label>
            </div>
        </form>

        <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border);text-align:center;font-size:11px;color:var(--muted)">
            Identifiants de démo: <strong>admin@kola.com</strong> / <strong>password</strong>
        </div>
    </div>

    <script>
        // URL de base (récupérée depuis le HTML)
        const BASE_URL = window.location.pathname.includes('/public/') 
            ? window.location.origin + window.location.pathname.split('/public/')[0] + '/'
            : window.location.origin + '/erp-kola/';

        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.querySelector('[name="email"]').value;
            const password = document.querySelector('[name="password"]').value;
            const btnSubmit = document.getElementById('btn-submit');
            
            // Désactiver le bouton
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Connexion en cours...';
            
            try {
                // Envoyer au serveur
                const response = await fetch(BASE_URL + 'public/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        email: email,
                        password: password
                    }),
                    credentials: 'same-origin' // Important pour les sessions
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('success-msg').textContent = 'Connexion réussie ! Redirection...';
                    document.getElementById('success-msg').style.display = 'block';
                    
                    // Rediriger vers le dashboard
                    setTimeout(() => {
                        window.location.href = BASE_URL + 'public/dashboard';
                    }, 1500);
                } else {
                    document.getElementById('error-msg').textContent = data.message || 'Erreur de connexion';
                    document.getElementById('error-msg').style.display = 'block';
                    
                    // Réactiver le bouton
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Connexion';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('error-msg').textContent = 'Erreur de connexion: ' + error.message;
                document.getElementById('error-msg').style.display = 'block';
                
                // Réactiver le bouton
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Connexion';
            }
        });
    </script>
</body>
</html>