// 1. Inicializa o Mercado Pago com sua Public Key
const bricksBuilder = mp.bricks();

// 2. Função principal para renderizar o formulário
const renderCardPaymentBrick = async () => {
  const settings = {
    initialization: {
      amount: totalParaPagar, // Variável vinda do PHP no pagar.php
      payer: {
        email: emailCliente, // Variável vinda do PHP no pagar.php
      },
    },
    customization: {
      visual: {
        style: {
          theme: "dark", // Mantém o visual All Black da Boxter
        },
      },
    },
    callbacks: {
      onReady: () => {
        console.log("Formulário pronto!");
        // Isso vai limpar tudo o que estiver dentro do container ANTES de mostrar o formulário
        // Assim, o spinner e o texto "Carregando" somem de vez.
        const container = document.getElementById("cardPaymentBrick_container");

        // Se houver algum parágrafo ou spinner de carregamento, nós removemos
        const loader = container.querySelector(".text-center");
        if (loader) loader.remove();
      },

      onSubmit: (formData) => {
        // --- LÓGICA DO SPINNER ---
        const btnPagar = document.querySelector(
          ".mp-bricks-status-bar__submit-button",
        );
        if (btnPagar) {
          btnPagar.classList.add("btn-loading");
          btnPagar.innerHTML =
            '<div class="spinner" style="display:inline-block"></div> Gravando Pedido...';
        }

        // AGORA ENVIAMOS APENAS OS DADOS DO CARTÃO (formData)
        // O PHP vai buscar o endereço sozinho na $_SESSION['entrega_temporaria']
        return new Promise((resolve, reject) => {
          fetch("processar_pagamento.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData),
          })
            .then(async (response) => {
              const result = await response.json();
              if (!response.ok || result.status === "error") {
                throw new Error(result.message || "Falha ao processar pagamento.");
              }
              return result;
            })
            .then((result) => {
              // Redirecionamento para a página de sucesso
              window.location.href =
                "obrigado.php?status=" +
                encodeURIComponent(result.status) +
                "&pedido=" +
                encodeURIComponent(result.pedido_id);
              resolve();
            })
            .catch((error) => {
              console.error("Erro no processamento:", error);
              alert(error.message || "NÃ£o foi possÃ­vel finalizar o pagamento.");
              if (btnPagar) {
                btnPagar.classList.remove("btn-loading");
                btnPagar.innerHTML = "Tentar Novamente";
              }
              reject();
            });
        });
      },

      onError: (error) => {
        console.error("Erro no Mercado Pago:", error);
      },
    },
  };

  window.cardPaymentBrickController = await bricksBuilder.create(
    "cardPayment",
    "cardPaymentBrick_container",
    settings,
  );
};

// 3. Executa a função
renderCardPaymentBrick();
