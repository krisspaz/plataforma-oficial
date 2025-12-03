import { test, expect } from '@playwright/test';

test('login flow', async ({ page }) => {
    await page.goto('/login');

    // Expect a title "to contain" a substring.
    await expect(page).toHaveTitle(/Plataforma Escolar/);

    // Fill login form
    await page.getByLabel('Email').fill('admin@school.com');
    await page.getByLabel('Contraseña').fill('password123');

    // Click login button
    await page.getByRole('button', { name: 'Iniciar Sesión' }).click();

    // Expect to be redirected to dashboard
    await expect(page).toHaveURL('/');
    await expect(page.getByText('Bienvenido, Admin')).toBeVisible();
});

test('responsive layout check', async ({ page }) => {
    await page.goto('/');

    // Check if sidebar is visible on desktop
    await expect(page.getByTestId('sidebar')).toBeVisible();

    // Resize to mobile
    await page.setViewportSize({ width: 375, height: 667 });

    // Sidebar should be hidden or collapsed
    await expect(page.getByTestId('sidebar')).toBeHidden();

    // Hamburger menu should be visible
    await expect(page.getByTestId('menu-button')).toBeVisible();
});
