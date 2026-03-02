import { test, expect } from '@playwright/test';

test('login falla con credenciales inválidas', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin/login');

  // Email incorrecto
  await page.getByRole('textbox', { name: 'Correo electrónico*' }).fill('jgjfyt@com');

  // Password incorrecto
  await page.getByRole('textbox', { name: 'Contraseña*' }).fill('456435d');

  // Click login
  await page.getByRole('button', { name: 'Entrar' }).click();

  //  Debe quedarse en login
  await expect(page).toHaveURL(/\/admin\/login$/);

  //  Debe mostrar mensaje de error
  await expect(page.locator('body')).toContainText(
    'Estas credenciales no coinciden'
  );

  //  No debe mostrarse el panel
  await expect(page.locator('aside')).toHaveCount(0);
});