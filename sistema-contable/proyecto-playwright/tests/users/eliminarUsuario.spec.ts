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

test('Eliminar usuario', async ({ page }) => {
  await login(page);
  await page.goto('/admin/users', { waitUntil: 'domcontentloaded' });

  await expect(page.getByRole('heading', { name: /usuarios/i })).toBeVisible();
  await expect(page.getByText(/no se encontraron registros/i)).toHaveCount(0);

  const firstRow = page.locator('table tbody tr').first();
  await expect(firstRow).toBeVisible();

  const deleteBtn = firstRow.getByRole('button', { name: /borrar|eliminar/i });
  await expect(deleteBtn).toBeVisible();
  await deleteBtn.click();

  const modal = page.locator('.fi-modal-window:visible').first();
  await expect(modal).toBeVisible({ timeout: 15000 });
  await expect(modal.getByRole('button', { name: /^borrar$|^eliminar$/i })).toBeVisible();
  await modal.getByRole('button', { name: /cancelar|no/i }).first().click();
  await expect(modal).toBeHidden({ timeout: 10000 });
});
