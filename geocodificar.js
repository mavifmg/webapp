async function buscarCoordenadasPorCep(cep, numero) {
    // Remove caracteres especiais do CEP (deixa apenas números)
    const cepLimpo = cep.replace(/\D/g, '');

    if (cepLimpo.length !== 8) {
        console.log("CEP inválido. Deve conter 8 dígitos.");
        return null;
    }

    try {
        console.log("1. Buscando dados do CEP...");
        // Etapa 1: Consulta na ViaCEP (gratuita e não exige token)
        const responseCep = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
        const dadosCep = await responseCep.json();

        if (dadosCep.erro) {
            console.log("CEP não encontrado.");
            return null;
        }

        const { logradouro, bairro, localidade, uf } = dadosCep;
        console.log(`Endereço base encontrado: ${logradouro}, ${bairro} - ${localidade}/${uf}`);

        // Etapa 2: Monta o endereço completo com o número para geocodificação
        const enderecoCompleto = `${numero}, ${logradouro}, ${localidade}, ${uf}, Brasil`;
        const urlGeo = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(enderecoCompleto)}&format=json&limit=1`;

        console.log("2. Buscando latitude e longitude...");
        const responseGeo = await fetch(urlGeo, {
            headers: { 'User-Agent': 'MeuProjetoGeocodeJS/1.0 (seu-email@exemplo.com)' }
        });
        const dadosGeo = await responseGeo.json();

        if (dadosGeo && dadosGeo.length > 0) {
            const local = dadosGeo[0];
            console.log("\n--- Resultado Final ---");
            console.log(`Endereço completo: ${local.display_name}`);
            console.log(`Latitude: ${local.lat}`);
            console.log(`Longitude: ${local.lon}`);

            return { lat: local.lat, lon: local.lon };
        } else {
            console.log("Não foi possível encontrar a coordenada exata para este número na rua.");
            return null;
        }

    } catch (error) {
        console.error("Erro durante o processo:", error);
    }
}

// Exemplo de uso:
buscarCoordenadasPorCep("36400-000", "150");