function setThousandSeparator(number) {
    let parts = number.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    return parts.join(".");
}
function CE(tagname, attr = null, methods = null) {
    let element = document.createElement(tagname)
    if (attr) {
        for ([key, value] of Object.entries(attr)) {
            element[key] = value
            // console.log(typeof value);
            if (typeof value == 'object') {
                for ([k, v] of Object.entries(value)) {
                    element[key][k] = v
                }
            }
        }
    }
    if (methods) {
        for ([key, value] of Object.entries(methods)) {
            if(Array.isArray(value)) {
                value.forEach(val => {
                    element[key](val)
                })
            } else {
                element[key](value)
            }
            
        }
    }
    return element
}