export function formatCurrency(value: number, currency = 'USD') {
    const parts = new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: currency,
    }).formatToParts(value);

    const currencyPartIndex = parts.findIndex(part => part.type === 'currency');
    const integerPartIndex = parts.findIndex(part => part.type === 'integer');

    if (currencyPartIndex !== -1 && integerPartIndex === currencyPartIndex + 1) {
        parts.splice(integerPartIndex, 0, { type: 'literal', value: ' ' });
    }

    return parts.map(p => p.value).join('');
}

export function formatCurrencyInput(
    e: Event,
    modelSetter?: (val: string) => void,
    currency = 'USD'
): void {
    const target = e.target as HTMLInputElement;
    // 1. Pega apenas os dígitos do valor do input
    const value = target.value.replace(/\D/g, '');

    if (value) {
        // 2. Converte os dígitos para um valor numérico (ex: '12345' -> 123.45)
        const numberValue = parseFloat(value) / 100;

        // 3. Usa a mesma lógica da função formatCurrency para obter a string formatada
        //    com o símbolo da moeda e o espaçamento correto.
        const formattedValue = formatCurrency(numberValue, currency);

        target.value = formattedValue;

        if (modelSetter) {
            // Atualiza o v-model com o valor formatado
            modelSetter(formattedValue);
        }
    } else {
        // Se o campo estiver vazio, limpa o valor
        target.value = '';
        if (modelSetter) {
            modelSetter('');
        }
    }
}
