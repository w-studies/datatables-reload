jQuery(function () {
  /**
   * define object para validação de forms
   */

  const formValidationObject = {
    errorClass: 'invalid-feedback',
    validClass: 'valid-feedback',
    errorElement: 'div',
    errorPlacement: function errorPlacement(error, element) {
      error.insertAfter(element)
    },
    highlight: function highlight(element, invalids, valids) {
      element.classList.add('is-invalid')
      element.classList.remove('is-valid')
      element.focus()
    },
    unhighlight: function unhighlight(element) {
      element.classList.remove('is-invalid')
      element.classList.add('is-valid')
    }
  }

  function delay(callback, ms) {
    let timer = 0
    return function () {
      let context = this,
        args = arguments
      clearTimeout(timer)
      timer = setTimeout(function () {
        callback.apply(context, args)
      }, ms || 0)
    }
  }
  /**
   * define o elemento datatables
   */

  let dataTables
  const tableData = $('table#table-data')
  // se existir
  if (tableData.length) {
    dataTables = tableData.DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
      },
      dom: '<"row small-gutters"<"col-auto"f><"col-auto ms-auto"l>>tipr',
      processing: true,
      serverSide: true,
      order: [],
      columnDefs: [
        {
          targets: 'no-sort',
          orderable: false
        }
      ],
      ajax: {
        url: tableData.data('href'),
        type: 'GET',
        dataSrc: json => json.data
      }
    })

    dataTables.on('init', function (e, settings, json) {
      $.fn.dataTable.Debounce = function (table, options) {
        const tableId = table.settings()[0].sTableId
        const inputSearch = $(`.dataTables_filter input[aria-controls="${tableId}"]`)
        // select the correct input field
        inputSearch
          .off() // Unbind previous default bindings
          .on(
            'input',
            delay(function (e) {
              // Bind our desired behavior
              table.search($(this).val()).draw()
              return
            }, 1000)
          ) // Set delay in milliseconds
      }

      const debounce = new $.fn.dataTable.Debounce(dataTables)
    })
  }

  const Modal = new bootstrap.Modal('div#modal')
  const form = $('form#formItems')
  // define as validações para o form na modal
  let modalFormValidation = form.validate(formValidationObject)

  // quando a modal abrir
  Modal._element.addEventListener('shown.bs.modal', () => {
    // focus no input
    form.find('input').eq(0)[0].focus()
  })

  // quando a modal fechar
  Modal._element.addEventListener('hidden.bs.modal', () => {
    // reset na validação
    form.find('.is-invalid').removeClass('is-invalid')
    modalFormValidation.resetForm()
  })

  /**
   * MODAL FORM SUBMIT
   */
  $('div#modal').on('submit', 'form#formItems', function (e) {
    e.preventDefault()
    const data = new FormData(this)
    const id = form.attr('data-id')

    if (id) {
      data.append('id', id)
    }

    $.ajax({
      url: form.prop('action'),
      type: form.prop('method'),
      dataType: 'JSON',
      data,
      processData: false,
      contentType: false,
      success: function success(response) {
        if (response.type === 'success') {
          // atualiza a tabela
          dataTables.ajax.reload(null, false)
          form[0].reset()
          // Modal.hide()
        } else {
          // desabilita a revalidação quando houver focusout
          modalFormValidation.settings.onfocusout = false

          modalFormValidation.showErrors(response.data)
        }
      }
    })
  })

  /**
   * editar
   */
  tableData.on('click', 'a.edit', async e => {
    e.preventDefault()
    const id = e.target.getAttribute('href')
    // busca no database o item com essa id
    form.attr('data-id', id)
    // busca os dados do item com essa id
    const response = await fetch(`api/items/${id}`).then(response => response.json())

    form.find('input').val(response.data[0][1])
    Modal.show()
  })

  /**
   * deletar
   */
  tableData.on('click', 'a.delete', function (e) {
    e.preventDefault()
    const id = e.target.getAttribute('href')
    alert(`deletar o item id: ${id}`)
  })

  /**
   * MODAL OPENER
   */
  $('[rel="modal-open"]').on('click', e => {
    e.preventDefault()
    form.removeAttr('data-id')
    form.find('input[name="item"]').val('')
    Modal.show()
  })
})
