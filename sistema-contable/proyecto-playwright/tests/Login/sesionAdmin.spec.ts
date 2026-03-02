import { test, expect } from '@playwright/test';

test('login admin', async ({ page }) => {
  test.setTimeout(90_000);

  await page.goto('http://127.0.0.1:8000/admin/login');

  await page.getByRole('textbox', { name: 'Correo electrónico*' }).fill('admin@sistema.com');
  await page.getByRole('textbox', { name: 'Contraseña*' }).fill('1234');

  await page.getByRole('button', { name: 'Entrar' }).click();

  // Espera que salga del login (si el login es correcto)
  const leftLogin = page.waitForURL(/\/admin(\/(?!login).*)?$/, { timeout: 15_000 }).then(() => true).catch(() => false);

  // Espera posible error 
  const sawError = page
    .locator('text=/credenciales|incorrect|inválid|invalid|demasiados intentos|too many/i')
    .first()
    .waitFor({ timeout: 15_000 })
    .then(() => true)
    .catch(() => false);

  const [ok, errorShown] = await Promise.all([leftLogin, sawError]);

  if (!ok) {
    // Si no salio del login, falla mostrando evidencia clara
    await expect(page).not.toHaveURL(/\/admin\/login$/);

    // y si hubo error
    if (errorShown) {
      await expect(page.locator('body')).toContainText(/credenciales|incorrect|inválid|invalid|intentos/i);
    }
  }

  // Si sí salió del login, valida que estamos en panel
  if (ok) {
    await expect(page).not.toHaveURL(/\/admin\/login$/);
    await expect(page.locator('body')).not.toContainText('Entrar');
  }
});


