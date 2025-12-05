import { test, expect } from '@playwright/test';

test.describe('Login Flow', () => {
    test('should display login form', async ({ page }) => {
        await page.goto('/login');

        await expect(page.getByRole('heading', { name: /iniciar sesión/i })).toBeVisible();
        await expect(page.getByLabel(/email|correo/i)).toBeVisible();
        await expect(page.getByLabel(/contraseña|password/i)).toBeVisible();
        await expect(page.getByRole('button', { name: /iniciar|entrar|login/i })).toBeVisible();
    });

    test('should show validation errors for empty form', async ({ page }) => {
        await page.goto('/login');

        await page.getByRole('button', { name: /iniciar|entrar|login/i }).click();

        await expect(page.getByText(/requerido|required/i)).toBeVisible();
    });

    test('should show error for invalid credentials', async ({ page }) => {
        await page.goto('/login');

        await page.getByLabel(/email|correo/i).fill('invalid@test.com');
        await page.getByLabel(/contraseña|password/i).fill('wrongpassword');
        await page.getByRole('button', { name: /iniciar|entrar|login/i }).click();

        await expect(page.getByText(/credenciales|inválidas|error/i)).toBeVisible({ timeout: 10000 });
    });

    test('should login successfully with valid credentials', async ({ page }) => {
        await page.goto('/login');

        await page.getByLabel(/email|correo/i).fill('admin@colegio.edu.gt');
        await page.getByLabel(/contraseña|password/i).fill('admin123');
        await page.getByRole('button', { name: /iniciar|entrar|login/i }).click();

        // Should redirect to dashboard
        await expect(page).toHaveURL(/dashboard/i, { timeout: 15000 });
    });

    test('should persist session after refresh', async ({ page }) => {
        await page.goto('/login');

        await page.getByLabel(/email|correo/i).fill('admin@colegio.edu.gt');
        await page.getByLabel(/contraseña|password/i).fill('admin123');
        await page.getByRole('button', { name: /iniciar|entrar|login/i }).click();

        await expect(page).toHaveURL(/dashboard/i, { timeout: 15000 });

        await page.reload();

        await expect(page).toHaveURL(/dashboard/i);
    });
});

test.describe('Dashboard', () => {
    test.beforeEach(async ({ page }) => {
        // Login before each test
        await page.goto('/login');
        await page.getByLabel(/email|correo/i).fill('admin@colegio.edu.gt');
        await page.getByLabel(/contraseña|password/i).fill('admin123');
        await page.getByRole('button', { name: /iniciar|entrar|login/i }).click();
        await expect(page).toHaveURL(/dashboard/i, { timeout: 15000 });
    });

    test('should display dashboard with statistics', async ({ page }) => {
        await expect(page.getByText(/estudiantes|students/i)).toBeVisible();
        await expect(page.getByText(/inscripciones|enrollments/i)).toBeVisible();
    });

    test('should navigate to students page', async ({ page }) => {
        await page.getByRole('link', { name: /estudiantes/i }).click();

        await expect(page).toHaveURL(/students/i);
    });

    test('should navigate to payments page', async ({ page }) => {
        await page.getByRole('link', { name: /pagos|payments/i }).click();

        await expect(page).toHaveURL(/payments/i);
    });
});
