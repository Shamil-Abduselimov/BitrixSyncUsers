document.addEventListener('DOMContentLoaded', () => {

    let info = JSON.parse(document.querySelector('#deal-info').value)

    let comment_btn = document.querySelector('button[data-action="comment"]')

    comment_btn.onclick = () => {
        !(document.querySelector('.comment_flat')) ? showComment() : hideComment()
    }
})

function showComment() {
    let place = CE('div',{
        className: 'comment_flat'
    })

    document.body.prepend(place)

    if(document.querySelector('#comment').value !== 'null') {
        let comments = JSON.parse(document.querySelector('#comment').value)
        if(comments.length) {
            comments.forEach(comment => newComment(comment)) 
        }
    }

    place.onclick = (e) => {
        if(e.target == place) {
            // console.log(e.pageX, e.pageY);
            let comment = {
                pos: {
                    left: e.pageX,
                    top: e.pageY,
                }
            }
            newComment(comment)
        }
    }

    
}
function hideComment() {
    let data = [];

    [...document.querySelector('.comment_flat').children].forEach(comment => {
        data.push({
            pos: {
                left: comment.style.left,
                top: comment.style.top
            },
            author: JSON.parse(comment.dataset.author),
            desc: comment.querySelector('textarea').value.trim()
        })
    })
    
    // console.log(data)

    document.querySelector('#comment').value = JSON.stringify(data)

    document.querySelector('button[data-action="comment"]').dataset.cnt = data.length

    document.querySelector('.comment_flat').remove()
}

function newComment({pos,author = null,desc = ''}) {
    let user = JSON.parse(document.querySelector('#user-info').value)
    // let domain = document.querySelector('#domain').value

    if(!author) {
        author = user
    }

    let new_comment_block = CE('div', {
        className: 'notification',
        style: {
            left: `${parseInt(pos.left)}px`,
            top: `${parseInt(pos.top)}px`,
        },
        dataset: {
            author: JSON.stringify(author)
        }
    },{
        append: [
            CE('div',{
                className: 'notification_header'
            },{
                append: [
                    CE('img', {
                        className: 'notification_header--author',
                        src: author.PERSONAL_PHOTO ?? `/bitrix/js/ui/icons/b24/images/ui-user.svg`
                    }),
                    CE('h2',{
                        className: 'notification_header--title',
                        innerText: 'Комментарий'
                    }),
                    author.ID == user.ID ? 
                    CE('button',{
                        className: 'notification_header--close',
                        innerText: 'Удалить',
                        onclick: () => {
                            new_comment_block.remove()
                        }
                    }) : ''
                ]
            }),
            CE('div',{
                className: 'notification_content',
            },{
                append: [
                    CE('textarea',{
                        className: 'notification_content--desc',
                        value: desc
                    })
                ]
            })
        ]
    })
    console.log();
    document.querySelector('.comment_flat').append(new_comment_block)
}