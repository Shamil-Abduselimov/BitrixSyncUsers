document.addEventListener("DOMContentLoaded", () => {
    let btns = document.querySelectorAll('.btn--submit')
    let status = document.querySelector('#status')
    let data = {}

    let loader = new Page()

    btns.forEach(btn => {
        btn.onclick = () => {
            data.info = {
                'bitrix': document.querySelector('#domain').value,
                'deal_info': JSON.parse(document.querySelector('#deal-info').value),
                'user_info': JSON.parse(document.querySelector('#user-info').value),
                'action': btn.dataset.action,
                'status': status.value
            }
            data.data = {
                'Tarif': Tarif('get'),
                'Coeff': Coeff('get'),
                'Fot': Fot('get'),
                'Parts': Parts('get'),
                'Goods': Goods('get'),
                'Comment': Comments('get')
            }
            
            loader.loadStart()

            console.log(data)

            let form_data = new FormData()
            form_data.append('info',JSON.stringify(data.info))
            form_data.append('data',JSON.stringify(data.data))
            let xhr = new XMLHttpRequest()
            xhr.open('POST', 'https://crm.vitamedrf.ru/local/application/marginality_calc/ajax/saveData.php')
            xhr.onload = () => {
                loader.loadStop()
                switch(btn.dataset.action) {
                    case 'send':
                        alert('Данные успешно отправлены!')
                        btn.innerHTML = 'На утверждении у РОПа...'
                        btn.setAttribute('disabled','')
                        btn.dataset.action = 'wait'
                        break
                    case 'save':
                        alert('Данные успешно сохранены!')
                        break
                    case 'allow':
                        alert('Разрешение на редактирование выдано!')
                        btn.innerHTML = 'Разрешение на изменение выдано...'
                        btn.setAttribute('disabled','')
                        btn.dataset.action = 'wait'
                        break
                }
                console.log(xhr.responseText);
            }
            xhr.send(form_data)
        }
    })

})