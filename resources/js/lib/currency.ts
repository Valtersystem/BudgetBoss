export function formatCurrencyInput(
    e: Event,
    modelSetter?: (val: string) => void
): void {
    const target = e.target as HTMLInputElement;
    let value = target.value.replace(/\D/g, '');

    if (value) {
        value = (parseFloat(value) / 100).toFixed(2);
        // Usando vírgula como separador decimal (euro style)
        target.value = '€ ' + value.replace('.', ',');
    } else {
        target.value = '';
    }

    if (modelSetter) {
        modelSetter(target.value);
    }
}
