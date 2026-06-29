<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $this->lang->line('user_login'); ?> : <?php echo $name; ?></title>
    <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php echo $this->setting_model->getAdminsmalllogo(); ?>" rel="shortcut icon" type="image/x-icon">
    
    <!-- CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/custom_login.css">
    <style>
        body { background: linear-gradient(135deg, #0097a7 0%, #00acc1 100%); }
        .container { border-radius: 25px; }
        .gauthenticate-form { display: none; }
        .notice-box { background: rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 15px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid var(--primary-color); }
        .notice-box h4 { margin-bottom: 8px; color: var(--primary-color); font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .remember-me { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 14px; color: #666; cursor: pointer; }
        .remember-me input { width: 18px; height: 18px; cursor: pointer; }
        .arabic-text { direction: rtl; text-align: right; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body class="user-theme">
    <div class="container">
        <div class="form-box login">
            <form role="form" class="login-form" action="javascript:void(0);">
                <?php echo $this->customlib->getCSRF(); ?>
                <img src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php echo $this->setting_model->getAdminlogo(); ?>" style="max-width: 150px; margin-bottom: 15px;" />
                <h1><?php echo $this->lang->line('user_login'); ?></h1>
                
                <div class="notice-box">
                    <h4><i class='bx bx-help-circle'></i> Aide à la connexion</h4>
                    <div style="margin-bottom: 5px;"><strong>FR:</strong> Accès Parents et Élèves.</div>
                    <div style="margin-bottom: 5px;"><strong>EN:</strong> Parent and Student Portal.</div>
                    <div class="arabic-text"><strong>AR:</strong> بوابة أولياء الأمور والطلاب.</div>
                </div>

                <div class="alert alert-danger error_message" style="display:none; margin-bottom: 15px;"></div>

                <div class="input-box">
                    <input type="text" name="username" id="username" placeholder="<?php echo $this->lang->line('username'); ?>" value="<?php echo set_value('username') ?>" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder="<?php echo $this->lang->line('password'); ?>" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>

                <label class="remember-me">
                    <input type="checkbox" id="remember">
                    <span><?php echo "Retenir le mot de passe"; ?></span>
                </label>

                <?php if($is_captcha){ ?>
                <div class="input-box" style="display: flex; gap: 10px; align-items: center;">
                    <span id="captcha_image"><?php echo $captcha_image; ?></span>
                    <span title='Refresh' class="bx bx-refresh" style="cursor:pointer; font-size: 24px;" onclick="refreshCaptcha()"></span>
                    <input type="text" name="captcha" placeholder="<?php echo $this->lang->line('captcha'); ?>" autocomplete="off" required style="flex: 1;">
                </div>
                <?php } ?>

                <div class="forgot-link">
                    <a href="<?php echo site_url('site/ufpassword') ?>"><?php echo $this->lang->line('forgot_password'); ?>?</a>
                </div>
                <button type="submit" class="btn" id="submit1"><?php echo $this->lang->line('sign_in'); ?></button>
            </form>

            <form role="form" class="gauthenticate-form" action="javascript:void(0);">
                <h1>Verification</h1>
                <div class="alert alert-danger error_message_code" style="display:none;"></div>
                <div class="input-box">
                    <input type="text" name="code" id="code" placeholder="6-digit code" required>
                    <i class='bx bxs-shield-quarter'></i>
                </div>
                <button type="submit" class="btn" id="submit2">Verify</button>
            </form>
        </div>

        <div class="form-box register">
            <div style="text-align: left; width: 100%; height: 100%; overflow-y: auto;">
                <h1>E-Victoire</h1>
                <?php if ($notice) { ?>
                    <?php foreach ($notice as $notice_value) { ?>
                        <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                            <h4 style="color: var(--primary-color); font-weight: 600;"><?php echo $notice_value['title']; ?></h4>
                            <p style="font-size: 13px;"><?php echo strip_tags($notice_value['description']); ?></p>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Bienvenue !</h1>
                <button class="btn login-btn">Retour</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>E-Victoire</h1>
                <button class="btn register-btn">Actualités</button>
            </div>
        </div>
    </div>

    <script src="<?php echo base_url(); ?>backend/usertemplate/assets/js/jquery-1.11.1.min.js"></script>
    <script src="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            if (localStorage.getItem('user_remember') === 'true') {
                $('#username').val(localStorage.getItem('user_username'));
                $('#password').val(atob(localStorage.getItem('user_password')));
                $('#remember').prop('checked', true);
            }

            const container = document.querySelector('.container');
            $('.register-btn').click(() => container.classList.add('active'));
            $('.login-btn').click(() => container.classList.remove('active'));

            $(document).on('submit', '.login-form', function(e) {
                e.preventDefault();
                var $this = $(this);
                var $btn = $("#submit1");
                
                if ($('#remember').is(':checked')) {
                    localStorage.setItem('user_username', $('#username').val());
                    localStorage.setItem('user_password', btoa($('#password').val()));
                    localStorage.setItem('user_remember', 'true');
                } else {
                    localStorage.removeItem('user_username');
                    localStorage.removeItem('user_password');
                    localStorage.setItem('user_remember', 'false');
                }

                $.ajax({
                    url: "<?php echo site_url('gauthenticate/verfiy_userlogin')?>",
                    type: "POST",
                    data: $this.serialize(),
                    dataType: 'json',
                    beforeSend: () => $btn.attr('disabled', true).text('Connexion...'),
                    success: function(response) {
                        if (response.status == 0) {
                            if (response.error.error_message) $('.error_message').html(response.error.error_message).show();
                        } else if (response.status == 2) {
                            if (!response.authenticator) window.location.href = response.redirect_to;
                            else $this.fadeOut(400, () => $('.gauthenticate-form').fadeIn());
                        }
                    },
                    error: () => alert("Erreur de connexion portail."),
                    complete: () => $btn.attr('disabled', false).text('Sign In')
                });
            });

            $(document).on('submit', '.gauthenticate-form', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "<?php echo site_url('gauthenticate/user_submit_login')?>",
                    type: "POST",
                    data: $('.login-form').serialize() + "&" + $(this).serialize(),
                    dataType: 'json',
                    success: (response) => {
                        if (response.status == 2) window.location.href = response.redirect_to;
                        else if (response.error) $('.error_message_code').html(response.error.error_message).show();
                    }
                });
            });
        });
    </script>
</body>
</html>
