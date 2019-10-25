import greet from "./greeting"

const text = greet('Moin')
console.log(greet('Hallo'))
document.querySelector('h1').textContent = greet('Moin Moin')