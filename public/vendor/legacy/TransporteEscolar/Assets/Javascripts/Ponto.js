var $j = jQuery.noConflict();

var $idField = $j('#id');

var submitForm = function (event) {
  if ($j('#cep_').val()) {
    if (!validateEndereco()) {
      return;
    }
  }
  submitFormExterno();
}

var $j = jQuery.noConflict();
var $idField = $j('#id');

// Estilo dos botões de mapa
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
  // Torna latitude/longitude readonly
  $j('#latitude').attr('readonly', true).css('background-color', '#DFDFDF');
  $j('#longitude').attr('readonly', true).css('background-color', '#DFDFDF');

  // HTML dos botões e campo de foto
  const customRow = `
    <tr>
      <td colspan='2'>
        <div id='map' style='height: 45px; text-align: left; width: 500px; margin-bottom: 4px;'>
          <button class="btn-mapa" id='loadMapButton' type='button'>Abrir mapa - Google</button>
          <button class="btn-mapa" id='loadOsmButton' type='button'>Abrir mapa - OSM</button>
        </div>
        <div class="form-group" style="margin-top: 5px;">
          <label for="photo" style="font-weight: 600;">Foto do ponto</label><br>
          <input type="file" name="photo" id="photo" accept="image/*" style="border: 1px solid #ccc; border-radius: 4px; padding: 6px; background-color: #f9f9f9; width: 100%; max-width: 300px;">
          <span style="font-size: 12px; color: #777;">* Formatos: jpeg, jpg, png, gif. Máx: 2MB</span>
          <div id="preview-foto-ponto" style="margin-top: 8px;"></div>
        </div>
      </td>
    </tr>
  `;

  // Adiciona linha de mapas e foto
  const $targetRow = $j('.tableDetalheLinhaSeparador').closest('tr');
  if ($targetRow.length > 0) {
    $targetRow.before(customRow);
  } else {
    $j('table').first().append(customRow);
  }


  $j(document).on('change', '#photo', function () {
    const file = this.files[0];
    if (file) {
      const formData = new FormData();
      formData.append('photo', file);
      formData.append('id_ponto', $j('#id').val()); // ou outro campo que contenha o id do ponto


      $j.ajax({
        url: '/upload-foto', // ou '/api/upload-foto' se for API
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        headers: {
          'X-CSRF-TOKEN': $j('meta[name="csrf-token"]').attr('content') // importante
        },
        success: function (response) {
          console.log('Foto salva em:', response.path);
        },
        error: function (xhr) {
          console.error('Erro ao enviar a foto:', xhr.responseText);
        }
      });
    }
  });

});

// Ações dos botões de mapa
$j(document).on('click', '#loadMapButton', function () {
  const lat = $j('#latitude').val();
  const lng = $j('#longitude').val();
  let url = '/googlemap';
  if (lat && lng) {
    url += `?lat=${lat}&lng=${lng}`;
  }
  window.open(url, '_blank');
});

$j(document).on('click', '#loadOsmButton', function () {
  const lat = $j('#latitude').val();
  const lng = $j('#longitude').val();
  let url = '/osmmap';
  if (lat && lng) {
    url += `?lat=${lat}&lng=${lng}`;
  }
  window.open(url, '_blank');
});

// Submit (caso continue usando manualmente)
var submitForm = function (event) {
  if ($j('#cep_').val()) {
    if (!validateEndereco()) {
      return;
    }
  }
  submitFormExterno();
};

// Manipuladores de resposta da API
resourceOptions.handlePost = function (dataResponse) {
  if (!dataResponse.any_error_msg) {
    setTimeout(() => {
      location.href = '/intranet/transporte_ponto_det.php?cod_ponto=' + resource.id();
    }, 500);
  } else {
    $submitButton.removeAttr('disabled').val('Gravar');
  }
};

resourceOptions.handlePut = function (dataResponse) {
  if (!dataResponse.any_error_msg) {
    setTimeout(() => {
      location.href = '/intranet/transporte_ponto_det.php?cod_ponto=' + resource.id();
    }, 500);
  } else {
    $submitButton.removeAttr('disabled').val('Gravar');
  }
};

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
    $j('#city_city').val(`${dataResponse.idmun} - ${dataResponse.municipio} (${dataResponse.sigla_uf})`);
  }
};
