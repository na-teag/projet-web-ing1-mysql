let products = []
let selectedId = -1
let token = ""

function getProduct(id) {
    return products.find(p => p.id === id)
}

async function getProducts(category) {
    result = await sendJSON("/catalog/products?category=" + category, {}, "GET")
    if(result.status === 200) {
        const jsonResponse = JSON.parse(result.responseText)
        const container = document.getElementById("container")
        for (let i = 0; i < jsonResponse.length; i++) {
            const product = jsonResponse[i]
			const product_name = product.name.charAt(0).toUpperCase() + product.name.slice(1)
            const productCard =
                `
                <div class="product" onclick="showProduct(${product.id})">
                    <div class="imgbox">
                        <img src="/product/${product.icon}" alt="icon"/>
                    </div>
                    <label class="infproduct">${product_name}</label>
                    <label class="infproduct">${product.unit_price}€</label>
                    <label class="infproduct" id="amount_${product.id}">En stock: ${product.amount - (product.in_cart === "???" ? 0 : product.in_cart)}</label>
                </div>
                `
            container.innerHTML += productCard
        }

        return jsonResponse
    }

    return undefined
}

async function loadProducts(t) {
    const params = new URLSearchParams(window.location.search)
    products = await getProducts(params.has("category") ? params.get("category") : 12)
    token = t
}

async function showProduct(id) {
    const details = document.getElementById("details")
    const icon = document.getElementById("icon")
    const name = document.getElementById("name")
    const price = document.getElementById("price")
    const available = document.getElementById("available")
    const inCart = document.getElementById("in_cart")
    const description = document.getElementById("description")
    const amount = document.getElementsByName("amount")[0]
    const add = document.getElementById("add")

    if(products === undefined) return
    let product = getProduct(id)

    if(product === undefined) return

    selectedId = id

    details.hidden = false
    icon.src = `/product/${product.icon}`
    name.innerHTML = product.name.charAt(0).toUpperCase() + product.name.slice(1)
    price.innerHTML = `${product.unit_price}€`
    available.innerHTML = product.amount
    inCart.innerHTML = product.in_cart
    description.innerHTML = product.description
    amount.value = 1
    add.disabled = product.in_cart >= product.amount
}

function closeProduct() {
    document.getElementById("details").hidden = true
}

async function addItem() {
    const amount = document.getElementsByName("amount")[0].value

    const result = await sendJSON("/cart/add", {
        product: selectedId,
        amount: amount,
        _token: token
    })

    const itemAdd = document.getElementById("add")
    const itemInCart = document.getElementById("in_cart")

    if(result.status === 200) {
        const jsonResponse = JSON.parse(result.responseText)
        const product = getProduct(selectedId)
        const productDiv = document.getElementById("amount_" + selectedId)

        switch (jsonResponse.state) {
            case "ok":
                itemAdd.disabled = false
                product.in_cart = jsonResponse.amount
                productDiv.innerHTML = "En stock: " + jsonResponse.amount
                break

            case "full":
                itemAdd.disabled = true
                product.in_cart = jsonResponse.amount
                productDiv.innerHTML = "En stock: " + jsonResponse.amount
                break
        }

        itemInCart.innerHTML = product.in_cart
        productDiv.innerHTML = "En stock: " + (product.amount - (product.in_cart === "???" ? 0 : product.in_cart))
    }

    if(result.status === 422) {
        const jsonResponse = JSON.parse(result.responseText)
        if(jsonResponse.hasOwnProperty("errors")) {
            const errors = Object.entries(jsonResponse.errors)
            for(const [name, error] of errors) {
                const elements = document.getElementsByName(name)
                for(let i = 0; i < elements.length; i++) {
                    const element = elements[i]
                    element.classList.add("invalid")
                }
                const errorDiv = document.getElementById(name + "_error")
                if(errorDiv !== null) errorDiv.innerHTML = `<li class="error">${error}</li>`
            }
        }
    }
}

async function removeItem() {
    const amount = document.getElementsByName("amount")[0].value

    const result = await sendJSON("/cart/remove", {
        product: selectedId,
        amount: amount,
        _token: token
    })

    const itemAdd = document.getElementById("add")
    const itemInCart = document.getElementById("in_cart")

    if(result.status === 200) {
        const jsonResponse = JSON.parse(result.responseText)
        const product = getProduct(selectedId)
        const productDiv = document.getElementById("amount_" + selectedId)

        switch (jsonResponse.state) {
            case "ok":
                itemAdd.disabled = false
                product.in_cart = jsonResponse.amount
                break

            case "full":
                itemAdd.disabled = true
                product.in_cart = jsonResponse.amount
                break

            case "doesNotExist":
            case "delete":
                itemAdd.disabled = false
                product.in_cart = 0
        }

        itemInCart.innerHTML = product.in_cart
        productDiv.innerHTML = "En stock: " + (product.amount - (product.in_cart === "???" ? 0 : product.in_cart))
    }

    if(result.status === 422) {
        const jsonResponse = JSON.parse(result.responseText)
        if(jsonResponse.hasOwnProperty("errors")) {
            const errors = Object.entries(jsonResponse.errors)
            for(const [name, error] of errors) {
                const elements = document.getElementsByName(name)
                for(let i = 0; i < elements.length; i++) {
                    const element = elements[i]
                    element.classList.add("invalid")
                }
                const errorDiv = document.getElementById(name + "_error")
                if(errorDiv !== null) errorDiv.innerHTML = `<li class="error">${error}</li>`
            }
        }
    }
}
