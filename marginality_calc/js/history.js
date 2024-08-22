document.addEventListener("DOMContentLoaded", () => {
    let btn_history = document.querySelector('[data-action="history"]')

    let xhr = new XMLHttpRequest()

    let page_history = new Page()
    btn_history.onclick = () => {
        let history = JSON.parse(document.querySelector('#history-info').value)
        let block = CE('div',{
            className: 'content_history'
        })

        history.forEach(elem => {
            let status = ''
            switch(elem.STATUS) {
                case 'send':
                    status = `Отправлен на утверждение`                    
                    break;                     
                case 'allow': 
                    status = `Отправлен на доработку`                
                    break;                
                case 'save':
                    status = `${elem.ALLOW_ID ? 'Утвержден' : 'Сохранен'}`                    
                    break;                    
            }
            let item = CE('div', {
                className: 'history_item',
                dataset: {
                    id: elem.ID
                }
            }, {
                append: [
                    CE('div', {
                        className: 'item_column',
                        innerHTML: `
                            <span class="item_column--title">Название</span>
                            <span class="item_column--value">${elem.TITLE}</span>
                        `,
                        dataset: {
                            field: 'TITLE'
                        }
                    }),
                    CE('div', {
                        className: 'item_column',
                        innerHTML: `
                            <span class="item_column--title">ФИО ответственного</span>
                            <span class="item_column--value">${[elem.ASSIGNED.LAST_NAME,elem.ASSIGNED.NAME,elem.ASSIGNED.SECOND_NAME].filter(n => n).join(' ')}</span>
                        `,
                        dataset: {
                            field: 'ASSIGNED'
                        }
                    }),
                    CE('div', {
                        className: 'item_column',
                        innerHTML: `
                            <span class="item_column--title">Дата расчета</span>
                            <span class="item_column--value">${elem.DATE}</span>
                        `,
                        dataset: {
                            field: 'DATE'
                        }
                    }),
                    CE('div', {
                        className: 'item_column',
                        innerHTML: `
                            <span class="item_column--title">Статус</span>
                            <span class="item_column--value">${status}</span>
                        `,                        
                        dataset: {
                            field: 'STATUS'
                        }
                    }),
                    CE('button', {
                        className: 'item_btn',
                        innerHTML: 'Просмотр',
                        onclick: (e) => {
                            let history = JSON.parse(document.querySelector('#history-info').value)
                            let calc_data = JSON.parse(history.filter(data => data.ID == e.target.parentElement.dataset.id)[0].CALC_DATA)
                            // console.log(calc_data)
                            Goods('set',calc_data)
                            page_history.close()
                        }
                    }),
                ]
            })
            block.append(item)
        })
        page_history.open('ИСТОРИЯ', block)
    }
})