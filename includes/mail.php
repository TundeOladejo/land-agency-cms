<?php
// FILE: includes/mail.php

function sendResetEmail($to, $link) {
    $subject = "Password Reset Request";
    $message = "
      <p>Hello,</p>
      <p>You requested to reset your password. Click the link below to continue:</p>
      <p><a href=\"$link\">Reset Password</a></p>
      <p>This link will expire in 30 minutes. If you didn't request this, ignore this email.</p>
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@land-agency-cms.local\r\n";

    return mail($to, $subject, $message, $headers);
  }

  function sendConfirmationEmail($to) {
    $subject = "Your Password Was Reset";
    $message = "
      <p>Hello,</p>
      <p>This is a confirmation that your password was changed successfully.</p>
      <p>If you did not do this, please contact support immediately.</p>
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@land-agency-cms.local\r\n";

    return mail($to, $subject, $message, $headers);
  }

  function sendWelcomeEmail($to, $password) {
    $subject = "Your Land Agency CMS Account";
    $message = "
      <p>Welcome!</p>
      <p>Your CMS account has been created.</p>
      <p><strong>Email:</strong> $to</p>
      <p><strong>Password:</strong> $password</p>
      <p>Please log in and change your password after first login.</p>
      <p><a href='http://localhost/land-agency-cms/public/index.php'>Login Here</a></p>
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@land-agency-cms.local\r\n";

    return mail($to, $subject, $message, $headers);
  }
