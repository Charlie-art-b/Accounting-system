import { test, expect } from '@playwright/test';

test('Listar y filtrar', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Clientes' }).click();
  await page.getByRole('button', { name: 'Filtrar' }).click();
  await page.getByLabel('Proveedor', { exact: true }).selectOption('4');
  await page.getByRole('button', { name: 'Aplicar filtros' }).click();
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).click();
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).dblclick();
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('C');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('C');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('');
});