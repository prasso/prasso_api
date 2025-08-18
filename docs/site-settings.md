# Site Settings

This document outlines the various settings available for configuring a site in Prasso.

## User Registration Settings

### `supports_registration`
- **Type**: Boolean
- **Default**: `true`
- **Description**: 
  - When `true`, users can register for an account directly through the registration page.
  - When `false`, the registration form will be hidden, and users cannot self-register.

### `invitation_only`
- **Type**: Boolean
- **Default**: `false`
- **Description**:
  - When `true`, users can only register if they have a valid invitation.
  - This setting works in conjunction with `supports_registration`:
    - If `supports_registration` is `false`, registration is completely disabled regardless of this setting.
    - If `supports_registration` is `true` and `invitation_only` is `true`, users must have a valid invitation link to register.

## How It Works

1. **Registration Page Logic**:
   - The registration page (`/register`) checks if `supports_registration` is `true`.
   - If `false`, the registration form is not rendered, effectively disabling self-registration.
   - If `invitation_only` is `true`, users must provide a valid invitation token to complete registration.

2. **Invitation Flow**:
   - Site administrators can generate invitation links for new users.
   - These links contain a unique token that validates the invitation.
   - When `invitation_only` is enabled, the registration form will only be accessible through these invitation links.

## Best Practices

- To completely disable user registration:
  - Set `supports_registration` to `false`
  - Set `invitation_only` to `false` (or any value, as it won't matter)

- To allow registration but only with invitations:
  - Set `supports_registration` to `true`
  - Set `invitation_only` to `true`

- To allow open registration:
  - Set `supports_registration` to `true`
  - Set `invitation_only` to `false`

## Related Files

- `app/Models/Site.php` - Site model with fillable attributes
- `resources/views/auth/register.blade.php` - Registration view with conditional rendering
- `app/Http/Controllers/Auth/RegisteredUserController.php` - Handles user registration logic
