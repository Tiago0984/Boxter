$(document).ready(function () {
  $(".nav-link").click(function () {
    $(".nav-link").removeClass("active");
    $(this).addClass("active");
  });
});

$(document).ready(function () {
  $(".slider-marcas").slick({
    slidesToShow: 3,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 2000,
    arrows: false,
    dots: false,
    infinite: true,
    responsive: [
      {
        breakpoint: 992,
        settings: { slidesToShow: 3 },
      },
      {
        breakpoint: 576,
        settings: { slidesToShow: 2 },
      },
    ],
  });
});

$(".slider-relatos").slick({
  slidesToShow: 3,
  slidesToScroll: 1,
  autoplay: true,
  autoplaySpeed: 4000,
  arrows: true,
  dots: true,
  responsive: [
    { breakpoint: 992, settings: { slidesToShow: 2 } },
    { breakpoint: 768, settings: { slidesToShow: 1, arrows: false } },
  ],
});

function normalizarTextoEndereco(valor) {
  return String(valor || "")
    .replace(/^\s*string\s*:\s*/i, "")
    .trim();
}

function formatarCep(valor) {
  const cep = String(valor || "")
    .replace(/\D/g, "")
    .slice(0, 8);
  if (cep.length > 5) {
    return cep.replace(/(\d{5})(\d{1,3})/, "$1-$2");
  }
  return cep;
}

function buscarCepViaCep(cep, onSuccess, onError) {
  $.getJSON(`https://viacep.com.br/ws/${cep}/json/`)
    .done(function (dados) {
      if (!dados || "erro" in dados) {
        onError();
        return;
      }
      onSuccess(dados);
    })
    .fail(onError);
}

$(document).on("input", "#cep, #ent_cep", function () {
  this.value = formatarCep(this.value);
});

function buscarCepCadastro() {
  const cep = $("#cep").val().replace(/\D/g, "");
  if (cep.length !== 8) return false;

  $("#logradouro, #bairro, #cidade, #uf").val("...");

  buscarCepViaCep(
    cep,
    function (dados) {
      $("#logradouro").val(normalizarTextoEndereco(dados.logradouro));
      $("#bairro").val(normalizarTextoEndereco(dados.bairro));
      $("#cidade").val(normalizarTextoEndereco(dados.localidade));
      $("#uf").val(normalizarTextoEndereco(dados.uf).toUpperCase().slice(0, 2));
      $("#numero").focus();
    },
    function () {
      alert("CEP nao encontrado.");
      $("#logradouro, #bairro, #cidade, #uf").val("");
    },
  );
  return true;
}

window.buscaCEP = function () {
  return buscarCepCadastro();
};

$(document).on("blur change", "#cep", function () {
  buscarCepCadastro();
});

$(document).on("input", "#cep", function () {
  const cep = $(this).val().replace(/\D/g, "");
  if (cep.length === 8) {
    buscarCepCadastro();
  }
});

// Adicione esta função ao arquivo animacao.js

$("#ent_cep").on("blur", function () {
  const cep = $(this).val().replace(/\D/g, "");
  if (cep.length !== 8) return;

  $("#ent_rua, #ent_bairro, #ent_cidade, #ent_uf").val("...");

  buscarCepViaCep(
    cep,
    function (dados) {
      $("#ent_rua").val(normalizarTextoEndereco(dados.logradouro));
      $("#ent_bairro").val(normalizarTextoEndereco(dados.bairro));
      $("#ent_cidade").val(normalizarTextoEndereco(dados.localidade));
      $("#ent_uf").val(
        normalizarTextoEndereco(dados.uf).toUpperCase().slice(0, 2),
      );
      $("#ent_num").focus();

      // 🆕 AGORA RECALCULA O FRETE AUTOMATICAMENTE
      if (typeof calcularFreteViaFrenet === "function") {
        calcularFreteViaFrenet(cep);
      }
    },
    function () {
      alert("CEP de entrega não encontrado.");
      $("#ent_rua, #ent_bairro, #ent_cidade, #ent_uf").val("");
    },
  );
});

// Também recalcula quando o usuário digita um CEP válido
$(document).on("input", "#ent_cep", function () {
  const cep = $(this).val().replace(/\D/g, "");
  if (cep.length === 8) {
    // Aguarda um pouco para o usuário terminar de digitar
    clearTimeout(window.cepTimeout);
    window.cepTimeout = setTimeout(() => {
      if (typeof calcularFreteViaFrenet === "function") {
        calcularFreteViaFrenet(cep);
      }
    }, 800);
  }
});
