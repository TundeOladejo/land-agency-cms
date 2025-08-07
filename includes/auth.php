<?php
// FILE: includes/auth.php
require_once 'db.php';

/**
 * Get user record by email (excluding soft-deleted)
 */
function getUserByEmail($email) {
  global $pdo;
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
  $stmt->execute([$email]);
  return $stmt->fetch();
}

/**
 * Log a failed login attempt
 */
function logFailedAttempt($email, $ip) {
  global $pdo;
  $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
  $stmt->execute([$email, $ip]);
}

/**
 * Check if a user is locked out due to too many failed attempts
 * Lockout period: 30 minutes, Max Attempts: 3
 */
function isLockedOut($email, $ip) {
  global $pdo;
  $stmt = $pdo->prepare("
    SELECT COUNT(*) FROM login_attempts
    WHERE email = ? AND ip_address = ?
      AND attempted_at > datetime('now', '-30 minutes')
  ");
  $stmt->execute([$email, $ip]);
  return $stmt->fetchColumn() >= 3;
}

/**
 * Clear login attempts after successful login
 */
function clearLoginAttempts($email, $ip) {
  global $pdo;
  $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ? AND ip_address = ?");
  $stmt->execute([$email, $ip]);
}

/**
 * Validate a password reset token
 */
function validateResetToken($token) {
  global $pdo;
  $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > datetime('now') AND used = 0");
  $stmt->execute([$token]);
  return $stmt->fetch();
}

/**
 * Mark a reset token as used
 */
function markTokenUsed($token) {
  global $pdo;
  $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
  $stmt->execute([$token]);
}

/**
 * Update a user's password securely
 */
function updateUserPassword($email, $hashedPassword) {
  global $pdo;
  $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = datetime('now') WHERE email = ?");
  $stmt->execute([$hashedPassword, $email]);
}
