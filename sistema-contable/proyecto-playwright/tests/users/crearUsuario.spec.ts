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

async function selectAllowedRole(page) {
  await page.getByRole('button', { name: /seleccione una opci/i }).click();

  const preferredRole = page.getByRole('option', { name: /asistente/i });
  if (await preferredRole.count()) {
    await preferredRole.first().click();
    return;
  }

  const fallbackRole = page.getByRole('option', { name: /sub-gerente|gerente/i });
  if (await fallbackRole.count()) {
    await fallbackRole.first().click();
    return;
  }

  await page.locator('[role="option"]').first().click();
}

test('Crear usuario', async ({ page }) => {
  const stamp = Date.now();
  const userName = `Usuario Playwright ${stamp}`;
  const userEmail = `usuario.playwright.${stamp}@test.com`;

  await login(page);
  await page.goto('/admin/users/create', { waitUntil: 'domcontentloaded' });

  await page.getByRole('textbox', { name: /nombre completo/i }).fill(userName);
  await page.getByRole('textbox', { name: /correo electr/i }).fill(userEmail);
  await page.getByRole('textbox', { name: /contrase/i }).first().fill('123456');
  await page.getByRole('textbox', { name: /confirmar contrase/i }).fill('123456');
  await selectAllowedRole(page);

  const crearBtn = page.getByRole('button', { name: /^crear$/i });
  await expect(crearBtn).toBeEnabled();
  await crearBtn.click();

  const modal = page.locator('.fi-modal-window:visible').first();
  await expect(modal).toBeVisible({ timeout: 15000 });
  await modal.getByRole('button', { name: /crear/i }).last().click();

  await expect(page).toHaveURL(/\/admin\/users(\/create)?/);
});

