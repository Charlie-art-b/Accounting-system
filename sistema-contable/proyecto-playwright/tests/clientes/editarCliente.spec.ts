import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Clientes' }).click();
  await page.getByRole('link', { name: 'Editar' }).nth(1).click();
  await page.getByRole('textbox', { name: 'Nombre*' }).click();
  await page.getByRole('textbox', { name: 'Nombre*' }).fill('');
  await page.getByRole('textbox', { name: 'Nombre*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nombre*' }).fill('J');
  await page.getByRole('textbox', { name: 'Nombre*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nombre*' }).fill('Juan');
  await page.getByRole('textbox', { name: 'Segundo apellido' }).click();
  await page.getByRole('textbox', { name: 'Segundo apellido' }).fill('');
  await page.getByRole('textbox', { name: 'Segundo apellido' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Segundo apellido' }).fill('R');
  await page.getByRole('textbox', { name: 'Segundo apellido' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Segundo apellido' }).fill('Rodrigues');
  await page.getByRole('textbox', { name: 'Primer apellido*' }).click();
  await page.getByRole('textbox', { name: 'Primer apellido*' }).fill('');
  await page.getByRole('textbox', { name: 'Primer apellido*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Primer apellido*' }).fill('F');
  await page.getByRole('textbox', { name: 'Primer apellido*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Primer apellido*' }).fill('Fernandez');
  await page.getByRole('button', { name: 'Guardar cambios' }).click();
  await page.getByRole('button', { name: 'Sí, guardar' }).click();
  await page.getByRole('link', { name: 'Cancelar' }).click();
});