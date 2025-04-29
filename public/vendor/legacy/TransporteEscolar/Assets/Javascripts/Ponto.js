var $idField = $j('#id');

var submitForm = function (event) {
  if ($j('#cep_').val()) {
    if (!validateEndereco()) {
      return;
    }
  }
  submitFormExterno();
}

resourceOptions.handlePost = function (dataResponse) {
  if (!dataResponse.any_error_msg)
    window.setTimeout(function () {
      document.location = '/intranet/transporte_ponto_det.php?cod_ponto=' + resource.id();
    }, 500);
  else
    $submitButton.removeAttr('disabled').val('Gravar');
}

resourceOptions.handlePut = function (dataResponse) {
  if (!dataResponse.any_error_msg)
    window.setTimeout(function () {
      document.location = '/intranet/transporte_ponto_det.php?cod_ponto=' + resource.id();
    }, 500);
  else
    $submitButton.removeAttr('disabled').val('Gravar');
}

resourceOptions.handleGet = function (dataResponse) {
  handleMessages(dataResponse.msgs);

  $deleteButton.removeAttr('disabled').show();

  $idField.val(dataResponse.id);
  $j('#desc').val(dataResponse.desc);
  $j('#postal_code').val(dataResponse.cep);
  $j('#latitude').val(dataResponse.latitude);
  $j('#longitude').val(dataResponse.longitude);

  if ($j('#postal_code').val()) {
    $j('#address').val(dataResponse.logradouro);
    $j('#number').val(dataResponse.numero);
    $j('#complement').val(dataResponse.complemento);
    $j('#neighborhood').val(dataResponse.bairro);
    $j('#city_id').val(dataResponse.idmun);
    $j('#city_city').val(dataResponse.idmun + ' - ' + dataResponse.municipio + ' (' + dataResponse.sigla_uf + ')');
  }
};


$j('<style>').text(`
  .btn-mapa {
     margin: 4px 0;
    padding: 7px 7px !important;
    color: #188ad1 !important;
    font-family: "Open Sans", sans-serif;
    font-size: 12px !important;
    background-color: #FFF !important;
    border: 1px solid #ccc;
    border-radius: 3px;
    cursor: pointer;
    
  }


`).appendTo('head');

$j(document).ready(function () {
  // Tornar lat/lng apenas leitura e cinza
  $j('#latitude').attr('readonly', 'true').css('background-color', '#DFDFDF');
  $j('#longitude').attr('readonly', 'true').css('background-color', '#DFDFDF');

  $j('<tr>').html(`
    <td colspan='2'>
      <div id='map' style='height: 45px; text-align: left;' width='500px'>
        <button class="btn-mapa" id='loadMapButton' type='button'>Abrir mapa</button>
      </div>
    </td>
  `).insertBefore($j('.tableDetalheLinhaSeparador').closest('tr'));


  // Substitui ação de submit
  window.setTimeout(function () {
    $submitButton.removeAttr('onclick');
    $submitButton.unbind('click');
    $submitButton.click(submitForm);
  }, 1000);
});

// Ação do botão de mapa
$j(document).on('click', '#loadMapButton', function () {
  const lat = $j('#latitude').val();
  const lng = $j('#longitude').val();

  let url = '/googlemap';
  if (lat && lng) {
    url += `?lat=${lat}&lng=${lng}`;
  }

  window.open(url, '_blank');
});
