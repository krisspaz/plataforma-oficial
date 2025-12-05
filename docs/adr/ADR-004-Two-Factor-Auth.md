# ADR-004: Two-Factor Authentication (2FA)

## Status
Accepted

## Date
2024-12-05

## Context
The platform handles sensitive student, payment, and contract data. Enhanced security measures are required to protect user accounts from unauthorized access.

## Decision
Implement Time-based One-Time Password (TOTP) 2FA with the following features:

### Components:
- `TwoFactorAuthService` - Core 2FA logic
- `TwoFactorController` - API endpoints
- `SecurityAuditListener` - Centralized security logging

### Features:
1. **TOTP Setup**
   - QR code generation for authenticator apps
   - Manual entry option
   
2. **Backup Codes**
   - 8 one-time recovery codes
   - BCrypt hashed storage
   - Auto-removal after use

3. **Security Logging**
   - Login success/failure tracking
   - Suspicious request detection
   - IP and user agent logging

### API Endpoints:
```
GET  /api/2fa/status       - Check 2FA status
POST /api/2fa/setup        - Start 2FA setup (returns QR)
POST /api/2fa/verify       - Verify code and enable
POST /api/2fa/disable      - Disable 2FA
POST /api/2fa/backup-codes - Regenerate backup codes
```

## Consequences

### Positive
- Significant security improvement
- Industry-standard TOTP implementation
- Backup codes for account recovery
- Comprehensive security audit logs

### Negative
- Additional login step for users
- Dependency on external authenticator apps
- Users can lock themselves out if they lose device

### Mitigations
- Backup codes provided during setup
- Admin override capability (future)
- Clear user documentation
