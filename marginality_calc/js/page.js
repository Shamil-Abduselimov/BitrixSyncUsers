class Page {
    // block = null
    // constructor() {
    //     this.block = CE('div',{
    //         className: 'form_block',
    //     })
    // }
    
    open(title, content, footer = null) {
        if (document.querySelector('.form_block')) this.close()

        let form_block = CE('div',{
            className: 'form_block',
        })
        let form_page = CE('div',{
            className: 'form_page',
        })
        let page_header = CE('div', {
            className: 'page_header',
        }, {
            append: [
                CE('span', {
                        className: "page--title",
                        innerText: title,
                        dataset: {
                            field: 'title'
                        }
                    }
                ),
                CE('span', {
                        className: "page--close",
                        onclick: () => {
                            this.close()
                        }
                    }
                )
            ]
        })
        let page_content = CE('div', { // from content
            className: "page_content"
        })
        let page_footer = CE('div', { // from content
            className: "page_footer"
        })

        if(typeof content == 'object') {
            page_content.append(content)
        } else {
            page_content.innerHTML = `${content}`
        }
    
        form_page.append(page_header, page_content, page_footer.innerHTML ? page_footer : '')
        form_block.append(form_page)
        document.body.append(form_block)
    }
    close() {
        document.querySelector('.form_block').remove()
    }
    loadStart() {
        if (document.querySelector('.form_load')) this.loadStop()

        let form_load = CE('div',{
            className: 'form_load',
            innerHTML: `<div class="load_animation">Loading...</div>`
        })
        document.body.append(form_load)
    }
    loadStop() {
        document.querySelector('.form_load').remove()
    }

}