$(function () {
    // Обработчик клика по элементам с классом .content_item
    $('.content_item').click(function () {
        const modal = $('#login-modal');
        modal.empty().modal({
			fadeDuration: 100,
			clickClose: false
		}).append(setForm(this.id));
        return false;
    });
});

function setForm(category_id) {
    const config = JSON.parse($('#config').val())[category_id];
    const header = $('<div>').addClass('form_header');
    const category = $('<div>').addClass('form_category');
    const content = $('<div>').addClass('form_content');

    if (config.categories) {
        header.html(`<span class="header--title">${config.title}</span>`);
        const subs = config.categories;
        const desc = $('<span>').addClass('category--desc');
        const select = createSelect(subs, desc, content);
        category.append([select, desc]);
    } else {
        header.html(`<span class="header--title" data-field="TITLE">${config.title}</span>`);
        content.append($.map(config.fields, getField));
        content.append(createSubmitButton(config));
    }

    return $('<div>').addClass('form_block').append([header, category, content]);
}

function createSelect(subs, desc, content) {
    return $('<select>')
        .addClass('category--title')
        .attr('data-field', 'TITLE')
        .html('<option value="" disabled selected>Выберите категорию</option>')
        .append($.map(subs, (sub, index) => $('<option>').text(sub.title).val(Number(index) + 1)))
        .change(function (e) {
            const sub = subs[Number(this.value) - 1];
            e.preventDefault();
            desc.text(sub.desc);
            content.empty().append($.map(sub.fields, getField)).append(createSubmitButton(sub));
        });
}

function createSubmitButton(config) {
    return $('<button>')
        .text('Отправить заявку')
        .addClass('content_btn')
        .attr('data-entity', config.entity)
        .click(() => {
                if(checkValidate()) sendData(config)
            }
        );
}

function getField(data) {
    const fieldData = JSON.parse($('#fields').val())[data.field];
    data = {...data, ...fieldData}
    if (data.group) {
        return $('<div>')
            .addClass('field_group')
            .append($('<span>').addClass('sub--title').text(data.title))
            .append($.map(data.group, getField));
    }

    const field = $('<div>')
        .addClass('field_item')
        .html(`<span class="item--title">${data.title} ${data.required ? '*' : ''}</span>`)
        .attr('required', data.required)
        .attr('data-field', data.field);

    const input = createInput(data);
    input.change(function () {
        const block = $(this).parent();
        if (block.attr('required')) {
            block.removeClass('error');
        }
    });

    field.append(input);
    return field;
}

function createInput(data) {
    let field = JSON.parse($('#fields').val())[data.field];
    let input;
    switch (data.type) {
        case "select":
            input = createSelectField(data);
            break;
        case 'textarea':
            input = createTextareaField(data);
            break;
        case 'groupbox':
            input = createGroupboxField(data);
            break;
        case 'department':
            input = createDepartmentField(data);
            break;
        default:
            input = createInputField(data);
            break;
    }

    return applyMask(input, data.mask);
}

function getFieldData(field, delimiter = '. ') {
    const result = $(`*[data-field="${field}"]`).map((_, elem) => {
        if ($(elem).hasClass('header--title')) {
            return $(elem).text();
        }
        if ($(elem).hasClass('category--title')) {
            return $(elem).find('option:selected').text();
        }
        if ($(elem).hasClass('field_item')) {
            const itemField = $(elem).find('.item--field');

            switch (itemField.attr('data-type')) {
                case 'file': return '***file***'
                case 'department': return itemField.find('option:selected').text().replace(/^-+\s*/, '')
                case 'groupbox':
                    if (itemField.find('input[type="checkbox"]').length) {
                        const checkedItems = itemField.find('input[type="checkbox"]:checked').map((_, checkbox) => {
                            const label = $(checkbox).parent('label');
                            return label.attr('data-value') ? label.attr('data-value') : label.text();
                        }).get();
                        if(checkedItems.length > 0 && itemField.find('label[data-value]').length > 0) {
                            delimiter = false
                            return checkedItems
                        } else {
                            return checkedItems.join(', ')
                        }
                    }
                    if (itemField.find('input[type="radio"]').length) {
                        const label = itemField.find('input[type="radio"]:checked').parent('label');
                        return label.attr('data-value') ? label.attr('data-value') : label.text();
                    }
                default: return field !== 'DESCRIPTION' ? itemField.val() : `${$(elem).find('.item--title').text()}: ${itemField.val()}`;
            }
        }
        return '';
    }).get();

    return delimiter ? result.join(delimiter) : result
}

function getFields() {
    const keys = $.unique($('*[data-field]').map((_, elem) => elem.dataset.field).get());
    const data = {};
    $.map(keys, key => {
        data[key] = key !== 'DESCRIPTION' ? getFieldData(key) : getFieldData(key, '<br>');
    });
    console.log('Collected fields data:', data);
    return data;
}

async function sendData(data) {
    $('#login-modal').hide();
    this.blur();

    const formData = new FormData();
    formData.append('entity', data.entity);
    formData.append('entity_id', data.entity_id);

    const fields = getFields();
    for (const key in fields) {
        if (fields.hasOwnProperty(key)) {
            if (fields[key] === '***file***') {
                formData.append(`fields[${key}]`, $(`[data-field="${key}"]`).find('input')[0].files[0]);
            } else {
                if(Array.isArray(fields[key])) {
                    fields[key].forEach(value => {
                        formData.append(`fields[${key}][]`, value);
                    });
                } else {
                    formData.append(`fields[${key}]`, fields[key]);
                }
            }
        }
    }

    console.log('FormData:', formData);

    try {
        const response = await $.ajax({
            type: "POST",
            url: `https://crm.vitamedrf.ru/local/application/hr/ajax/new.php?${getAuthData()}`,
            data: formData,
            processData: false,
            contentType: false
        });
        console.log(JSON.parse(response));
        showModal('success');
    } catch (error) {
        console.log(error);
        showModal('error');
    }
}

function getAuthData() {
    const connection = document.querySelector('#connection').value;
    const params = new URLSearchParams(JSON.parse(connection));
    return params.toString();
}

function showModal(type, response = {}) {
    $('#login-modal')
        .empty()
        .append(message(type, response))
        .modal({ fadeDuration: 100 });
}

function message(type, response = {}) {
    const title = {
        success: 'Заявка успешно отправлена!',
        error: 'Ошибка при отправке!<br>Обратитесь к администратору службы ТП.'
    };

    const error_log = type === 'error' ? `
        <div class="message_error">
            <span class="error--name"><b>Ошибка: </b>${response.error}</span>
            <span class="error--desc"><b>Описание ошибки: </b>${response.error_message}</span>
        </div>` : '';

    return `
        <div class="form_message">
            <img src="img/icons/${type}.png" alt="" class="message--icon" />
            <span class="message--title">${title[type]}</span>
            ${error_log}
        </div>`;
}

function checkValidate() {
    const fields = $('.field_item[required] .item--field')
    let validate = true
    $.each(fields, (index, field) => {
        let elem = $(field)
        elem.removeClass('error')
        switch(elem.attr('data-type')) {
            case 'groupbox':
                if(!elem.find('input:checked').length) {
                    elem.parent().addClass('error')
                    validate = false
                }
                break;
            default:
                if(!elem.val()) {
                    elem.parent().addClass('error')
                    validate = false
                }
                break;
        }
    })

    return validate
}