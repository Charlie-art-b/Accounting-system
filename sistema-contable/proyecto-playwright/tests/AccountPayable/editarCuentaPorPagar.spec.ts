import { test, expect } from '@playwright/test';

test('Editar Cuenta por Pagar', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Cuentas Por Pagar' }).click();
  await page.getByRole('link', { name: 'Editar' }).click();
  await page.getByLabel('Términos de Pago*').selectOption('cash');
  await page.getByRole('button', { name: 'Guardar cambios' }).click();
  await page.getByRole('button', { name: 'Cancelar' }).click();
});