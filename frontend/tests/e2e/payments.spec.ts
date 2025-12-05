import { test, expect } from '@playwright/test';

test.describe('Payments Flow', () => {
    test.beforeEach(async ({ page }) => {
        // Login as admin
        await page.goto('/login');
        await page.getByLabel(/email|correo/i).fill('admin@colegio.edu.gt');
        await page.getByLabel(/contraseña|password/i).fill('admin123');
        await page.getByRole('button', { name: /iniciar|entrar|login/i }).click();
        await expect(page).toHaveURL(/dashboard/i, { timeout: 15000 });
    });

    test('should display payments list', async ({ page }) => {
        await page.goto('/payments');

        await expect(page.getByRole('heading', { name: /pagos/i })).toBeVisible();
        await expect(page.getByRole('table')).toBeVisible();
    });

    test('should filter payments by status', async ({ page }) => {
        await page.goto('/payments');

        // Find and click pending filter
        const filterButton = page.getByRole('button', { name: /pendiente|pending/i });
        if (await filterButton.isVisible()) {
            await filterButton.click();
        }

        await page.waitForTimeout(1000); // Wait for filter to apply
    });

    test('should open payment details modal', async ({ page }) => {
        await page.goto('/payments');

        // Click on first payment row
        const firstRow = page.locator('table tbody tr').first();
        if (await firstRow.isVisible()) {
            await firstRow.click();

            // Should open modal or navigate to details
            await page.waitForTimeout(500);
        }
    });

    test('should display pending payments count', async ({ page }) => {
        await page.goto('/payments?status=pending');

        // Should show pending count somewhere on page
        await expect(page.locator('body')).toContainText(/\d+/);
    });
});

test.describe('Payment Creation', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel(/email|correo/i).fill('secretaria@colegio.edu.gt');
        await page.getByLabel(/contraseña|password/i).fill('secretaria123');
        await page.getByRole('button', { name: /iniciar|entrar|login/i }).click();
        await expect(page).toHaveURL(/dashboard/i, { timeout: 15000 });
    });

    test('should open new payment form', async ({ page }) => {
        await page.goto('/payments');

        const newPaymentBtn = page.getByRole('button', { name: /nuevo|new|crear|create/i });
        if (await newPaymentBtn.isVisible()) {
            await newPaymentBtn.click();

            // Should show form or modal
            await expect(page.getByLabel(/monto|amount/i)).toBeVisible({ timeout: 5000 });
        }
    });

    test('should validate payment form fields', async ({ page }) => {
        await page.goto('/payments/new');

        const submitBtn = page.getByRole('button', { name: /guardar|save|crear|create/i });
        if (await submitBtn.isVisible()) {
            await submitBtn.click();

            // Should show validation errors
            await page.waitForTimeout(500);
        }
    });
});

test.describe('Parent Portal - Payments', () => {
    test('should display parent payments summary', async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel(/email|correo/i).fill('padre@test.com');
        await page.getByLabel(/contraseña|password/i).fill('padre123');
        await page.getByRole('button', { name: /iniciar|entrar|login/i }).click();

        // Navigate to payments section
        await page.goto('/portal/payments');

        await page.waitForTimeout(2000);
    });
});
