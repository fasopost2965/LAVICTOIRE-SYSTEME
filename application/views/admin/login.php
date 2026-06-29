<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $this->lang->line('admin_login'); ?> : <?php echo $name; ?></title>
    <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php echo $this->setting_model->getAdminsmalllogo(); ?>" rel="shortcut icon" type="image/x-icon">
    
    <!-- CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/custom_login.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #3f51b5 100%);
        }
        .container {
            border-radius: 25px;
        }
    </style>
</head>
<body class="admin-theme">
    <div class="container">
        <div class="form-box login">
            <form action="<?php echo site_url('site/login') ?>" method="post">
                <?php echo $this->customlib->getCSRF(); ?>
                <img src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php echo $this->setting_model->getAdminlogo(); ?>" style="max-width: 150px; margin-bottom: 20px;" />
                <h1><?php echo $this->lang->line('admin_login'); ?></h1>
                
                <?php
                if (isset($error_message)) {
                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                }
                if ($this->session->flashdata('message')) {
                    echo "<div class='alert alert-success'>" . $this->session->flashdata('message') . "</div>";
                    $this->session->unset_userdata('message'); 
                }
                if ($this->session->flashdata('disable_message')) {
                    echo "<div class='alert alert-danger'>" . $this->session->flashdata('disable_message') . "</div>";
                    $this->session->unset_userdata('disable_message'); 
                }
                ?>

                <div class="input-box">
                    <input type="text" name="username" placeholder="<?php echo $this->lang->line('username'); ?>" value="<?php echo set_value('username') ?>" required>
                    <i class='bx bxs-user'></i>
                    <span class="text-danger"><?php echo form_error('username'); ?></span>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="<?php echo $this->lang->line('password'); ?>" value="<?php echo set_value('password') ?>" required>
                    <i class='bx bxs-lock-alt'></i>
                    <span class="text-danger"><?php echo form_error('password'); ?></span>
                </div>

                <?php if($is_captcha){ ?>
                <div class="input-box" style="display: flex; gap: 10px; align-items: center;">
                    <span id="captcha_image"><?php echo $captcha_image; ?></span>
                    <span title='Refresh' class="bx bx-refresh" style="cursor:pointer; font-size: 24px;" onclick="refreshCaptcha()"></span>
                    <input type="text" name="captcha" placeholder="<?php echo $this->lang->line('captcha'); ?>" autocomplete="off" required style="flex: 1;">
                </div>
                <span class="text-danger"><?php echo form_error('captcha'); ?></span>
                <?php } ?>

                <div class="forgot-link">
                    <a href="<?php echo site_url('site/forgotpassword') ?>"><?php echo $this->lang->line('forgot_password'); ?>?</a>
                </div>
                <button type="submit" class="btn"><?php echo $this->lang->line('sign_in'); ?></button>
            </form>
        </div>

        <div class="form-box register">
            <div style="text-align: left; width: 100%; height: 100%; overflow-y: auto; padding-right: 10px;">
                <h1><?php echo $this->lang->line('whats_new_in'); ?> <?php echo $school['name']; ?></h1>
                <?php if ($notice) { ?>
                    <?php foreach ($notice as $notice_value) { ?>
                        <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                            <h4 style="color: var(--primary-color); font-weight: 600;"><?php echo $notice_value['title']; ?></h4>
                            <p style="font-size: 13px; line-height: 1.5;">
                                <?php
                                $string = strip_tags($notice_value['description']);
                                if (mb_strlen($string, 'UTF-8') > 150) {
                                    $stringCut = mb_substr($string, 0, 150, 'UTF-8');
                                    $endPoint = mb_strrpos($stringCut, ' ', 0, 'UTF-8');
                                    $string = $endPoint !== false ? mb_substr($stringCut, 0, $endPoint, 'UTF-8') : $stringCut;
                                    $string .= '... <a href="' . site_url('read/' . $notice_value['slug']) . '" target="_blank" style="color: var(--primary-color); font-weight: 600;">' . $this->lang->line('read_more') . '</a>';
                                }
                                echo $string;
                                ?>
                            </p>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No recent news available.</p>
                <?php } ?>
            </div>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Welcome!</h1>
                <p>Click here to return to the login screen.</p>
                <button class="btn login-btn">Back to Login</button>
                
                <div class="instructions" style="margin-top: 40px; text-align: center;">
                    <p style="font-style: italic; opacity: 0.8;">Admin Portal v2.0</p>
                </div>
            </div>
            <div class="toggle-panel toggle-right">
                <h1><?php echo $school['name']; ?></h1>
                <p>Administrative Access Only</p>
                
                <div class="instructions">
                    <h4><i class='bx bx-info-circle'></i> Instructions</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;">
                            <strong>EN:</strong> Use your official school credentials to login.
                        </li>
                        <li style="margin-bottom: 10px;">
                            <strong>FR:</strong> Utilisez vos identifiants officiels pour vous connecter.
                        </li>
                        <li class="arabic-text" style="margin-bottom: 10px;">
                            <strong>AR:</strong> استخدم بيانات الاعتماد الرسمية للدخول.
                        </li>
                    </ul>
                </div>

                <button class="btn register-btn" style="margin-top: 20px;">What's New?</button>
            </div>
        </div>
    </div>

    <script src="<?php echo base_url(); ?>backend/usertemplate/assets/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript">
        const container = document.querySelector('.container');
        const registerBtn = document.querySelector('.register-btn');
        const loginBtn = document.querySelector('.login-btn');

        registerBtn.addEventListener('click', () => {
            container.classList.add('active');
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });

        function refreshCaptcha(){
            $.ajax({
                type: "POST",
                url: "<?php echo base_url('site/refreshCaptcha'); ?>",
                data: {},
                success: function(captcha){
                    $("#captcha_image").html(captcha);
                }
            });
        }
    </script>
</body>
</html>
