# Payment Gateway Pix for GiveWP

## Project Structure

```
payment-gateway-pix-for-givewp/
├── payment-gateway-pix-for-givewp.php   # Plugin bootstrap (metadata, constants, activation hooks)
├── uninstall.php                        # Uninstall handler
├── index.php                            # Entry guard
├── Admin/
│   ├── PGPFGForGivewpAdmin.php          # Admin-specific hooks & settings rendering
│   ├── index.php
│   ├── css/                             # Admin stylesheets
│   ├── images/                          # Admin images
│   ├── js/                              # Admin scripts
│   └── templates/                       # Admin templates
├── Includes/
│   ├── PGPFGForGivewp.php               # Core plugin class (loader, DI container)
│   ├── PGPFGForGivewpLoader.php         # Hook registrar
│   ├── PGPFGForGivewpi18n.php           # Internationalization
│   ├── PGPFGForGivewpActivator.php      # Activation routines
│   ├── PGPFGForGivewpDeactivator.php    # Deactivation routines
│   ├── PGPFGGatewayClass.php            # Gateway abstraction
│   ├── PGPFGHelperClass.php             # Helper utilities
│   ├── PGPFGivePaghiperHelper.php       # PagHiper integration helper
│   └── index.php
├── Public/
│   ├── PGPFGForGivewpPublic.php         # Frontend/public-facing hooks
│   ├── PGPFGForPaghiperPixPage.php      # PIX payment page handler
│   ├── PGPFGGatewayClass.php            # Public gateway logic
│   ├── PGPFGGatewayPaghiperAbstractPayment.php  # Abstract payment gateway for PagHiper
│   ├── PGPFGPaghiperPix.php             # PagHiper PIX gateway
│   ├── PGPFGPaghiperSlip.php            # PagHiper boleto gateway
│   ├── index.php
│   ├── css/                             # Public stylesheets
│   ├── js/                              # Public scripts
│   ├── partials/                        # Template partials
│   └── views/                           # Public views
├── Languages/                           # .pot / .po translation files
├── vendor/                              # Composer dependencies
├── composer.json
└── .github/workflows/
```

## Namespace & Autoload

- **PSR-4 root:** `Pgpfg\`
- **Mappings:**
  - `Pgpfg\PGPFGForGivewp\Includes\` → `Includes/`
  - `Pgpfg\PGPFGForGivewp\Admin\` → `Admin/`
  - `Pgpfg\PGPFGForGivewp\PublicView\` → `Public/`

## Architecture & SOLID Principles

**Single Responsibility Principle (SRP)**
- Each class has one reason to change — separate concerns (Admin, Public, Includes, gateway classes)
- Use dedicated classes: `*Admin.php` for admin settings, `*Public.php` for frontend, `*Activator.php` for installation
- Functions should do one thing well — split large functions into smaller, focused ones

**Open/Closed Principle (OCP)**
- Extend functionality through hooks and filters, not by modifying existing code
- Use WordPress action/filter hooks: `add_action()`, `add_filter()`, `apply_filters()`, `do_action()`
- Gateway classes extend the abstract base (`PGPFGGatewayPaghiperAbstractPayment`) for new payment methods

**Liskov Substitution Principle (LSP)**
- Child classes must be substitutable for parent classes without breaking functionality
- Implement interfaces consistently across gateway classes

**Interface Segregation Principle (ISP)**
- Create small, focused interfaces rather than large monolithic ones
- Separate admin interfaces from public/gateway interfaces

**Dependency Inversion Principle (DIP)**
- Depend on abstractions, not concrete implementations
- Use dependency injection where possible, especially for external services (PagHiper API, etc.)

## Development Methodology

- Test-Driven Development (TDD)-based development.

## Code Standards

**WordPress Coding Standards**
- Follow WordPress 6.5+ / PHP 8.2+, JavaScript, CSS coding standards strictly
- Use WordPress nonce verification: `wp_verify_nonce()` for all form submissions
- Sanitize all inputs: `sanitize_text_field()`, `sanitize_email()`, etc.
- Escape all outputs: `esc_html()`, `esc_attr()`, `esc_url()`, etc.
- Internationalize all user-facing strings: `__()`, `_e()`, `_n()`

**Naming Conventions**
- Text Domain: `payment-gateway-pix-for-givewp`
- Classes: `PGPFGForGivewp*` (PascalCase, prefix `PGPFG`)
- Functions: `pgpfg_pix_*` (snake_case)
- Hooks: `pgpfg_pix_*` (snake_case)
- Constants: `PGPFG_PIX_*` (UPPER_SNAKE)
- Asset handles (CSS/JS): `payment-gateway-pix-for-givewp`
- `@package` doc tag: `PGPFGForGivewp`

## Testing Requirements

**Unit Tests**
- Write PHPUnit tests for all business logic, gateway logic, and utility functions
- Test WordPress hooks and filters behavior
- Mock external dependencies (GiveWP, PagHiper API, WordPress functions)
- Aim for 80%+ code coverage on critical paths

**Integration Tests**
- Test plugin activation/deactivation scenarios
- Test compatibility with GiveWP core updates
- Validate payment flow and donation data processing workflows

**Frontend Tests**
- Test JavaScript functionality with Jest or similar
- Validate payment form behavior in GiveWP donation forms
- Test responsive design and cross-browser compatibility

**Test Structure**

```php
// File: tests/unit/test-class-name.php
class Test_Class_Name extends WP_UnitTestCase {
    public function setUp(): void {
        parent::setUp();
        // Setup test data
    }

    public function test_specific_functionality() {
        // Arrange, Act, Assert pattern
    }
}
```

## RTK & Caveman Token Optimization

**RTK (Rust Token Killer) Context**
- The local environment has RTK active globally (`rtk init -g`). It intercepts and compresses terminal logs, test outputs, and system contexts before they reach the LLM.
- Expect concise, pre-parsed logs. Never request full verbose outputs if a structured RTK snippet has been provided.

**Caveman Communication Style (Strict Token Saving)**
- Act under strict **Caveman Mode** constraints for all chat interactions to minimize output token consumption.
- Drop all conversational fillers, polite introductions, greetings, and post-code summaries.
- Deliver technical facts using short, blunt, direct sentence fragments.
- Prioritize raw code blocks, diffs, and immediate solutions over friendly prose.

*Example of expected output:*
> "Fix: Missing nonce validation in PGPFGForGivewpAdmin.php. Add `wp_verify_nonce()` before processing."

**Copilot Generation & Architectural Alignment**
- When asked to generate code or fix a bug, output the structure immediately, strictly adhering to the SOLID principles and WordPress 6.5+ standards defined above.
- All code generated must automatically apply the `PGPFGForGivewp*` class prefixes, `pgpfg_pix_*` function/hook prefixes, and `Pgpfg\PGPFGForGivewp\*` namespace.
- Automatically embed strict security validation (nonce, sanitization, escaping) without needing explicit reminders.

## Build Commands

```bash
# Install dependencies
composer install

# Static analysis
vendor/bin/phan
```

## WordPress Plugin Specifics

**Hooks Priority**
- Use appropriate hook priorities to ensure correct execution order
- Document why specific priorities are chosen
- Test hook interactions with GiveWP and other popular plugins

**Database Operations**
- Use `$wpdb` prepared statements for custom queries
- Leverage WordPress meta APIs when possible
- Create proper database cleanup routines in `uninstall.php`

**Asset Management**
- Use `wp_enqueue_script()` and `wp_enqueue_style()` properly
- Include asset versioning for cache busting
- Minimize HTTP requests with proper concatenation/minification

## Error Handling

- Use `WP_Error` for recoverable errors
- Log errors appropriately without exposing sensitive information
- Provide user-friendly error messages on donation forms
- Implement graceful degradation for optional payment features

## Performance

- Cache expensive operations using WordPress transients
- Use lazy loading for admin-only functionality
- Optimize database queries — avoid N+1 problems
- Profile JavaScript performance in donation form flows
