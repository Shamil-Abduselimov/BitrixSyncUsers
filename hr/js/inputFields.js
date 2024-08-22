function createSelectField(data) {
    const select = $('<select>').addClass('item--field').attr('data-type', data.type);
    if (data.multiple) {
        select.attr('multiple', 'multiple');
    }
    select.append('<option value="" disabled selected>Выберите...</option>');
    $.each(data.value, (index, value) => {
        select.append($('<option>').attr('value', index).text(value));
    });
    return select;
}

function createTextareaField(data) {
    return $('<textarea>').addClass('item--field').attr('rows', 5).attr('data-type', data.type);
}

function createInputField(data) {
    return $('<input>').addClass('item--field').attr('type', data.type).attr('data-type', data.type);
}

function createGroupboxField(data) {
    const groupbox = $('<div>').addClass('item--field').attr('data-type', data.type);
    const inputType = data.multiple ? 'checkbox' : 'radio';
    $.each(data.value, (index, value) => {
        const input = $('<input>').attr('type', inputType).attr('name', data.field);
        const label = $('<label>').attr('data-value', index).append(input).append($('<span>').text(value));
        groupbox.append(label);
    });
    return groupbox;
}

function createDepartmentField(data) {
    const departments = JSON.parse($('#departments').val())

    function buildDepartmentHierarchy(departments, parentId = null, level = 0) {
        let result = [];
        departments
            .filter(department => department.PARENT === parentId || (parentId === null && !department.hasOwnProperty('PARENT')))
            .forEach(department => {
                const isParent = departments.some(dep => dep.PARENT === department.ID);
                result.push({
                    id: department.ID,
                    name: department.NAME,
                    level: level,
                    isParent: isParent
                });
                result = result.concat(buildDepartmentHierarchy(departments, department.ID, level + 1));
            });
        return result;
    }

    const select = $('<select>').addClass('item--field').attr('data-type', data.type);
    if (data.multiple) {
        select.attr('multiple', 'multiple');
    }
    select.append('<option value="" disabled selected>Выберите...</option>');

    const departmentHierarchy = buildDepartmentHierarchy(departments);

    departmentHierarchy.forEach(department => {
        const option = $('<option>')
            .attr('value', department.id)
            .attr('data-level', department.level)
            .text('-'.repeat(department.level) + ' ' + department.name);
        if (department.isParent) {
            option.css('font-weight', 'bold');
        }
        select.append(option);
    });

    return select;
}

function applyMask(input, mask) {
    if (mask) {
        input.mask(mask).attr('placeholder', mask);
    }
    return input;
}