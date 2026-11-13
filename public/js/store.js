document.addEventListener('submit', (event) => {
const form = event.target;
if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
    event.preventDefault();
  }
});

const shipping = document.querySelector('[name="shipping_service_id"]');

if (shipping) {
  const currencyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 2,
  });

  const costNode = document.querySelector('[data-shipping-total]');
  const totalNode = document.querySelector('[data-order-total]');
  const subtotalEl = document.querySelector('[data-subtotal]');

  const updateTotal = () => {
    const selectedOption = shipping.selectedOptions[0];
    const cost = Number(selectedOption?.dataset.cost || 0);
    const subtotal = Number(subtotalEl?.dataset.subtotal || 0);

    if (costNode) {
      costNode.textContent = shipping.value ? currencyFormatter.format(cost) : 'Pilih ekspedisi';
    }
    
    if (totalNode) {
      totalNode.textContent = currencyFormatter.format(subtotal + cost);
    }
  };

  shipping.addEventListener('change', updateTotal);
  updateTotal();
}