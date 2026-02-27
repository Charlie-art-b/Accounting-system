import { test, expect, Page } from '@playwright/test';

const BASE_URL = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const ADMIN_EMAIL = process.env.PLAYWRIGHT_ADMIN_EMAIL ?? 'admin@sistema.com';
const ADMIN_PASSWORD = process.env.PLAYWRIGHT_ADMIN_PASSWORD ?? '1234';

async function loginIfNeeded(page: Page) {
  await page.goto(`${BASE_URL}/admin`);

  if (page.url().includes('/login')) {
    await page.getByRole('textbox', { name: /Correo electr/i }).fill(ADMIN_EMAIL);
    await page.getByRole('textbox', { name: /Contrase/i }).fill(ADMIN_PASSWORD);
    await page.getByRole('button', { name: /Entrar/i }).click();
  }

  await expect(page).toHaveURL(/\/admin/);
}

async function openAccountingAccounts(page: Page) {
  const principalToggle = page.getByRole('button', { name: /PRINCIPAL/i }).first();

  if (await principalToggle.isVisible().catch(() => false)) {
    const isExpanded = await principalToggle.getAttribute('aria-expanded');

    if (isExpanded === 'false') {
      await principalToggle.click();
    }
  }

  const navLink = page.getByRole('link', { name: /Cuentas Contables/i }).first();
  await expect(navLink).toBeVisible({ timeout: 15000 });
  await navLink.click();
  await expect(page).toHaveURL(/\/admin\/accounting-accounts/);
}

async function selectFirstOptionInField(page: Page, labelText: string) {
  const combo = page.getByRole('combobox', { name: new RegExp(labelText, 'i') }).first();

  if (await combo.isVisible().catch(() => false)) {
    await combo.selectOption({ index: 1 });
    return;
  }

  const field = page
    .locator('.fi-fo-field-wrp')
    .filter({ has: page.getByText(labelText, { exact: false }) })
    .first();

  await field.getByRole('button').first().click();
  await page.keyboard.press('ArrowDown');
  await page.keyboard.press('Enter');
}

test.describe('Cuentas Contables - Front E2E', () => {
  test.setTimeout(90000);

  test('listar y buscar cuentas contables', async ({ page }) => {
    await loginIfNeeded(page);
    await openAccountingAccounts(page);

    await expect(page.getByText(/Cuentas Contables/i).first()).toBeVisible();

    const searchInput = page
      .locator('input[type="search"], input[placeholder*="squeda"], input[placeholder*="queda"]')
      .first();

    await searchInput.fill('Caja');
    await searchInput.clear();
  });

  test('crear cuenta contable', async ({ page }) => {
    await loginIfNeeded(page);
    await openAccountingAccounts(page);
    await page.locator('a[href*="/accounting-accounts/create"]').first().click();
    await expect(page).toHaveURL(/\/admin\/accounting-accounts\/create/);

    const uniqueCode = `ACC-E2E-${Date.now()}`;
    const accountName = `Cuenta E2E ${Date.now()}`;

    await selectFirstOptionInField(page, 'Cliente');
    await page.getByRole('textbox', { name: /Códig|CÃ³dig/i }).fill(uniqueCode);
    await page.getByRole('textbox', { name: /Nombre/i }).fill(accountName);
    await selectFirstOptionInField(page, 'Tipo');
    await page.getByRole('textbox', { name: /Sección|SecciÃ³n/i }).fill('Balance General');
    await selectFirstOptionInField(page, 'Naturaleza');

    await page.getByRole('button', { name: /Crear/i }).first().click();
    await expect(page).toHaveURL(/\/admin\/accounting-accounts$/, { timeout: 15000 });
  });

  test('editar cuenta contable', async ({ page }) => {
    await loginIfNeeded(page);
    await openAccountingAccounts(page);
    await page.locator('a[href*="/accounting-accounts/create"]').first().click();

    const code = `ACC-EDIT-${Date.now()}`;
    const baseName = `Cuenta Base ${Date.now()}`;
    const editedName = `${baseName} Editada`;

    await selectFirstOptionInField(page, 'Cliente');
    await page.getByRole('textbox', { name: /Códig|CÃ³dig/i }).fill(code);
    await page.getByRole('textbox', { name: /Nombre/i }).fill(baseName);
    await selectFirstOptionInField(page, 'Tipo');
    await selectFirstOptionInField(page, 'Naturaleza');
    await page.getByRole('button', { name: /Crear/i }).first().click();

    await openAccountingAccounts(page);
    const searchInput = page
      .locator('input[type="search"], input[placeholder*="squeda"], input[placeholder*="queda"]')
      .first();
    await searchInput.fill(code);
    await page.getByRole('link', { name: /Editar/i }).first().click();

    await page.getByRole('textbox', { name: /Nombre/i }).fill(editedName);
    await page.getByRole('button', { name: /Guardar cambios/i }).first().click();
    await expect(page).toHaveURL(/\/admin\/accounting-accounts$/);
    await expect(page.getByText(editedName).first()).toBeVisible();
  });
});
