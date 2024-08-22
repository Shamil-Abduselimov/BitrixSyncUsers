document.addEventListener("DOMContentLoaded", () => {
    GetData()
})

function Tarif(action = null, set_data = null) {
    const tarif_data = JSON.parse(document.querySelector('#tarif').value)['TARIF']

    let place = document.querySelector('#tarif-1')
    let schedule = document.querySelector('#tarif-2')
    let time = document.querySelector('#tarif-3')
    let med_period = document.querySelector('#tarif-4')
    let med_now = document.querySelector('#tarif-5')

    Object.values(time.options).forEach(elem => {
        if (!(elem.value in tarif_data[place.value]['tarif'][schedule.value])) { elem.setAttribute('hidden', '') } else { elem.removeAttribute('hidden') }
    })

    let tarif = Number(tarif_data[place.value]['tarif'][schedule.value][time.value]) * Number(med_now.value)


    if (time.value == "12" || time.value == "24") {
        tarif = Math.trunc(Number(tarif) * (schedule.value / 10))
    }
    place.onchange = schedule.onchange = time.onchange = med_period.oninput = med_now.oninput = () => {
        med_period.value = med_period.value < med_period.min ? med_period.min : med_period.value
        med_now.value = med_now.value < med_now.min ? med_now.min : med_now.value
        Goods()
    }
    if (!action) {
        return Number(tarif)
    } else {
        switch (action) {
            case 'get':
                let send_data = {
                    'tarif-1': place.value,
                    'tarif-2': schedule.value,
                    'tarif-3': time.value,
                    'tarif-4': med_period.value,
                    'tarif-5': med_now.value,
                }
                return send_data
            case 'set':
                if (set_data) {
                    let keys = Object.keys(set_data)
                    keys.forEach(key => {
                        switch (set_data[key]) {
                            case 'moscow': set_data[key] = 0; break;
                            case 'mosobl': set_data[key] = 1; break;
                        }
                        document.querySelector(`#${key}`).value = set_data[key]
                    })
                }
                return Tarif()
        }
    }
}
function Coeff(action = null, set_data = null) {
    let inputs = document.querySelectorAll('.container_block[data-block="coeff"] table tbody td:first-child input')
    let total = document.querySelector('span[data-result="coeff--total"]')
    let med_period = Number(document.querySelector('#tarif-4').value)
    inputs.forEach(input => {
        let temp = input.closest('tr').querySelector('.elem_total')
        temp.innerHTML = `${temp.innerHTML.replace(/ .*/, '')} &#10005; ${med_period}`
        input.onchange = () => {
            Goods()
        }
    })

    total.innerHTML = [...inputs].filter(input => input.checked).reduce((acc, i) => acc + (Number(i.closest('tr').querySelector('.elem_total').innerHTML.replace(/ .*/, '')) * med_period), 0).toFixed(2)
    if (!action) {
        return Number(total.innerHTML)
    } else {
        switch (action) {
            case 'get':
                let send_data = {
                    'coeff-1': document.querySelector('#coeff-1').checked,
                    'coeff-2': document.querySelector('#coeff-2').checked,
                    'coeff-3': document.querySelector('#coeff-3').checked,
                    'coeff-4': document.querySelector('#coeff-4').checked,
                    'coeff-5': document.querySelector('#coeff-5').checked,
                }
                return send_data
            case 'set':
                if (set_data) {
                    let keys = Object.keys(set_data)
                    keys.forEach(key => {
                        document.querySelector(`#${key}`).checked = set_data[key]
                    })
                }
                return Coeff()
        }
    }
}
function Fot(action = null, set_data = null) {
    const config = JSON.parse(document.querySelector('#tarif').value)

    let mrot = Number(config.MROT) * Number(document.querySelector('#tarif-5').value) // МРОТ
    let otpusk_check = document.querySelector('[data-block="fot"] .block_content input')
    let option = document.querySelector('[data-block="fot"] .block_header select')
    otpusk_check.onchange = option.onchange = () => {
        Goods()
    }
    let block = {
        stavka: document.querySelector('[data-block="fot"] .block_content [data-field="stavka"]'),
        zarplata: document.querySelector('[data-block="fot"] .block_content [data-field="zarplata"]'),
        otpusk: document.querySelector('[data-block="fot"] .block_content [data-field="otpusk"]'),
        accident: document.querySelector('[data-block="fot"] .block_content [data-field="accident"]'),
        fot: document.querySelector('[data-block="fot"] .block_content [data-field="fot"]'),
        total: document.querySelector('[data-block="fot"] .block_content [data-field="total"]')
    }

    let zarplata = (Tarif(action, set_data ? set_data['Tarif'] : null) * 1.13).toFixed(2) // зарплата
    let otpusk = otpusk_check.checked && option.value == 'td' ? (zarplata / 12).toFixed(2) : 0 // отпуск
    let accident = option.value == 'td' && Number(zarplata) <= mrot ? (Number(zarplata) * 0.002).toFixed(2) : 0 // страховка от НС
    if(!config.FOT) {
        accident = option.value == 'td' ? (Number(zarplata) * 0.002).toFixed(2) : 0
    }
    let fot = Number(zarplata) + Number(otpusk) + Number(accident) // ФОТ

    if(config.FOT) {
        if (fot <= mrot) { // Если ЗП + отпуск больше МРОТ, то 30%, иначе - 15%
            fot = (Number(fot) * 1.3).toFixed(2)
            block.fot.querySelector('.item--title').innerHTML = 'ФОТ (30%)'
            block.accident.classList.remove('denied')
        } else {
            fot = (Number(fot) * 1.15).toFixed(2)
            block.fot.querySelector('.item--title').innerHTML = 'ФОТ (15%)'
            block.accident.classList.add('denied')
        }
    } else {
        fot = (Number(fot) * 1.3).toFixed(2)
        block.fot.querySelector('.item--title').innerHTML = 'ФОТ (30%)'
        block.accident.classList.remove('denied')
    }


    switch (option.value) {
        case 'td':
            block.otpusk.classList.remove('denied')
            otpusk_check.disabled = false

            break;
        case 'gph':
            block.otpusk.classList.add('denied')
            block.accident.classList.add('denied')
            otpusk_check.disabled = true
            otpusk_check.checked = false

            break;
        case 'other':
            block.otpusk.classList.add('denied')
            block.accident.classList.add('denied')
            otpusk_check.disabled = true
            otpusk_check.checked = false

            block.fot.querySelector('.item--title').innerHTML = 'ФОТ (0%)'

            zarplata = (Tarif(action, set_data ? set_data['Tarif'] : null) * 1.15).toFixed(2)
            fot = Number(zarplata)

            break;
    }

    block.stavka.querySelector('.item--value').innerHTML = `${setThousandSeparator(Tarif(action, set_data ? set_data['Tarif'] : null))} руб.`
    block.zarplata.querySelector('.item--value').innerHTML = `${setThousandSeparator(zarplata)} руб.`
    block.otpusk.querySelector('.item--value').innerHTML = `${setThousandSeparator(otpusk)} руб.`
    block.accident.querySelector('.item--value').innerHTML = `${setThousandSeparator(accident)} руб.`
    block.fot.querySelector('.item--value').innerHTML = `${setThousandSeparator(fot)} руб.`
    block.total.querySelector('.item--value').innerHTML = `${setThousandSeparator(Number(fot) + Number(Coeff(action, set_data ? set_data['Coeff'] : null)))} руб.`

    if (!action) {
        return Number(fot) + Number(Coeff())
    } else {
        switch (action) {
            case 'get':
                let send_data = {
                    'fot-option': document.querySelector('#fot-option').value,
                    'fot-otpusk': document.querySelector('#fot-otpusk').checked,
                }
                return send_data
            case 'set':
                if (set_data['Fot']) {
                    document.querySelector('#fot-option').value = set_data['Fot']['fot-option']
                    document.querySelector('#fot-otpusk').checked = set_data['Fot']['fot-otpusk']
                }
                return Fot()
        }
    }
}
function Parts(action = null, set_data = null) {

    const parts = JSON.parse(document.querySelector('#tarif').value)['PARTS']

    let inputs = document.querySelectorAll('.container_block[data-block="parts"] table tbody td:first-child input')
    let staff_count = document.querySelector('.container_block[data-block="parts"] .block_header input[type="number"]')
    let date_count = document.querySelector('#tarif-2').value
    let total = document.querySelector('span[data-result="parts--total"]')

    let parts_data = []

    staff_count.oninput = () => {
        staff_count.value = staff_count.value < staff_count.min ? staff_count.min : staff_count.value
        Goods()
    }

    for (let i = 0; i < parts.length; i++) {
        const part = parts[i];

        let row = inputs[i].closest('tr')
        let row_count = row.querySelector('.elem_count').children[0]
        let row_price = row.querySelector('.elem_price').children[0]
        let row_total = row.querySelector('.elem_total')

        if (!part.params.use_day) {
            row_count.value = row_count.disabled ? Math.ceil(Number(staff_count.value) / Number(part.params.count)) : row_count.value
        } else {
            row_count.value = row_count.disabled ? Math.ceil((Number(staff_count.value) * Number(date_count)) / 10) : row_count.value
        }

        if (typeof part.cost == 'object') {
            for ([key, value] of Object.entries(part.cost)) {
                let min = key.split('-')[0]
                let max = key.split('-')[1]

                row_price.value = value

                if ((min > max) || (min <= Number(staff_count.value) && Number(staff_count.value) <= max)) break
            }
        }

        row_total.innerHTML = (Number(row_count.value) * Number(row_price.value)).toFixed(2)

        inputs[i].onchange = row_count.oninput = row_price.oninput = () => {
            Goods()
        }

        parts_data[i] = {
            checked: inputs[i].checked,
            count: row_count.value,
            price: row_price.value
        }
    }

    total.innerHTML = [...inputs].filter(input => input.checked).reduce((acc, i) => acc + Number(i.closest('tr').querySelector('.elem_total').innerHTML), 0).toFixed(2)

    if (!action) {
        return Number(total.innerHTML)
    } else {
        switch (action) {
            case 'get':
                let send_data = {
                    'staff-count': document.querySelector('#staff-count').value,
                    'table-data': parts_data,
                }
                return send_data
            case 'set':
                if (set_data['Parts']) {
                    document.querySelector('#staff-count').value = set_data['Parts']['staff-count']
                    let rows = document.querySelectorAll('[data-block="parts"] table tbody tr')

                    for (let i = 0; i < rows.length; i++) {
                        const row = rows[i];
                        let row_checked = row.querySelector('.elem_check').children[0]
                        let row_count = row.querySelector('.elem_count').children[0]
                        let row_price = row.querySelector('.elem_price').children[0]

                        row_checked.checked = set_data['Parts']['table-data'][i].checked
                        row_count.value = set_data['Parts']['table-data'][i].count
                        row_price.value = set_data['Parts']['table-data'][i].price
                    }
                }
                return Parts()
        }
    }
}
function Goods(action = null, set_data = null) {

    let good = document.querySelector('[data-block="goods"] .content_item:last-child')
    let percent = good.querySelector('#good-percent')

    let sum = Number(Fot(action == 'set' ? 'set' : null, set_data))
        + Number(Parts(action == 'set' ? 'set' : null, set_data))
    let total = null
    let marginality = (sum * (Number(percent.value) / 100)).toFixed(2)
    total = (Number(sum) + Number(marginality)).toFixed(2)

    good.querySelector('.item--goods').innerHTML = `Маржинальность: ${setThousandSeparator(marginality)} руб.`
    good.querySelector('.item--total').innerHTML = `Цена для клиента: ${setThousandSeparator(total)} руб.`

    let status = {
        main: document.querySelector('[data-block="goods"] .content_sub:first-child .content_item:first-child .item--status')
    }

    if (percent.value < 15 || total < 20000) {
        status.main.className = 'item--status low'
        status.main.innerHTML = 'Низкая маржинальность! Необходимо повысить процент либо уточнить в комментарии причину!'
    } else if ((15 <= percent.value && percent.value <= 20) && total >= 20000) {
        status.main.className = 'item--status middle'
        status.main.innerHTML = 'Средняя маржинальность.'
    } else {
        status.main.className = 'item--status high'
        status.main.innerHTML = 'Высокая маржинальность.'
    }

    percent.oninput = () => {
        Goods()
    }

    // Comments(action == 'set' ? 'set' : null,set_data)

    switch (action) {
        case 'get':
            let send_data = {
                'percent': percent.value,
                'total': total
            }
            return send_data
        case 'set':
            if (set_data['Goods']) {
                percent.value = set_data['Goods']['percent']
            }
            Goods()
    }
    // console.log(total);
}
function Comments(action = null, set_data = null) {
    let comment = document.querySelector('#comment')
    switch (action) {
        case 'get':
            let send_data = {
                'comment-value': JSON.parse(comment.value),
            }
            return send_data
        case 'set':
            if (set_data['Comment']) {
                comment.value = set_data['Comment']['comment-value']
            }
            break;
    }
}

function GetData() {
    let deal = JSON.parse(document.querySelector('#deal-info').value)

    console.log(deal);

    if (deal.info) {
        console.log(JSON.parse(deal.info.CALC_DATA));
        Goods('set', JSON.parse(deal.info.CALC_DATA))
    } else {
        Goods()
    }
}
