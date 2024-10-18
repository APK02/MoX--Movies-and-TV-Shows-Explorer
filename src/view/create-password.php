<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mochiy+Pop+P+One&display=swap" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Modern+Antiqua&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/login/login.css" />
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <title>Create password</title>
</head>
<body>

<div class="login-page">
      <div class="login-page__image-container">
        <img class="login-page__image" src="../assets/formImg.png" alt="login" />
        <div class="login-page__tagline">
          <h2 class="login-page__tagline-subtitle">The all in one movies platform</h2>
          <h1 class="login-page__tagline-title">MOX MOVIES</h1>
        </div>
      </div>
      <div class="login-page__form-container">
        <div class="login-page__form" id="login-page__form">
    <?php


    require "../api/repositories/ResetPasswordRepository.php";
    require "../api/vendor/autoload.php";
    $resetPasswordRepository = new ResetPasswordRepository();

    if ($_GET['key'] && $_GET['token']) {
        $userId = $_GET['key'];
        $token = $_GET['token'];
        $result = $resetPasswordRepository->findByUserId($userId);
        $curDate = date("Y-m-d H:i:s");
        if ($result !== null && $result["expiry"] >= $curDate) {
            ?>
            <h2 class="login-page__form-title">Create new password!</h2>
          <p class="login-page__form-description">Your password must be different from your previous used passwords</p>
            <input type="hidden" id="userId" name="userId" value="<?php echo $userId; ?>">
            <label for="password" class="login-form__label">New Password</label>
            <div class="login-form__input-wrapper">
              <input type="password" placeholder="Password" id="password" minlength="8" required class="login-form__input" autocomplete="new-password"/>
            </div>
            <label for="con_password" class="login-form__label">Confirm Password</label>
            <div class="login-form__input-wrapper">
              <input type="password" placeholder="Confirm password" id="con_password" minlength="8" required class="login-form__input" autocomplete="new-password"/>
            </div>
            <input class="login-form__submit-button" type="submit" value="Reset" onclick="reset()">Reset</input>
            <?php
        }
    } else { ?>
        <p class="not_found"> This forget password link has expired</p>
        <?php
    }
    ?>
      </div>
      </div>
</div>
<script src="../scripts/create-password.js"></script>
</body>
</html>