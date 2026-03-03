import { test, expect } from '@playwright/test';

async function login(page) {
  await page.goto('/admin/login', { waitUntil: 'domcontentloaded' });

  const emailInput = page.getByRole('textbox', { name: /correo electr/i });

  if ((await emailInput.count()) > 0) {
    await emailInput.fill('admin@sistema.com');
    await page.getByRole('textbox', { name: /contrase/i }).fill('1234');
    await Promise.all([
      page.waitForURL(/\/admin($|\/(?!login).*)/, { timeout: 20000 }),
      page.getByRole('button', { name: /entrar/i }).click(),
    ]);
  }
}

test('Editar usuario', async ({ page }) => {
  await login(page);
  await page.goto('/admin/users', { waitUntil: 'domcontentloaded' });

  await expect(page.getByRole('heading', { name: /usuarios/i })).toBeVisible();
  await expect(page.getByText(/no se encontraron registros/i)).toHaveCount(0);

  const firstRow = page.locator('table tbody tr').first();
  await expect(firstRow).toBeVisible();

  const editLink = firstRow.getByRole('link', { name: /editar/i });
  await expect(editLink).toBeVisible();
  await editLink.click();

  await expect(page).toHaveURL(/\/admin\/users\/\d+\/edit/);
  await expect(page.getByRole('button', { name: /guardar/i })).toBeVisible();
});
