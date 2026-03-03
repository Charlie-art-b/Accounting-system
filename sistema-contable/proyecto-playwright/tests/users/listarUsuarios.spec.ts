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

test('Listar usuarios', async ({ page }) => {
  await login(page);
  await page.goto('/admin/users', { waitUntil: 'domcontentloaded' });

  await expect(page.getByRole('heading', { name: /usuarios/i })).toBeVisible();
  await expect(page.getByRole('columnheader', { name: /nombre/i })).toBeVisible();
  await expect(page.getByRole('columnheader', { name: /correo/i })).toBeVisible();
  await expect(page.getByRole('columnheader', { name: /rol/i })).toBeVisible();
});

