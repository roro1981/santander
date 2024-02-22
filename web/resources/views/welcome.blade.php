

<button id="consumirServicioBtn">Core</button>

<div id="popup" style="display: none;">
    <p id="popupContent"></p>
</div>

<script>  
document.getElementById('consumirServicioBtn').addEventListener('click', function() {
        var datos = {
            'uuid': generarUUID(),
            'order': {
                'id': '000100',
                'product_id': '000200',
                'method_id': '000300',
                'url_confirmation': 'https://www.comercio.cl/confirmacion',
                'url_return': 'https://tebi4tbxq0.execute-api.us-west-2.amazonaws.com/QA/santander/v1/redirect',
                'attempt_number': '1',
                'amount': '1199.25',
                'subject': 'Pago por compra de productos',
                'expiration': 1693418602,
                'currency': '999',
                'extra_params': [
                    {
                        'key': 'Número de factura',
                        'value': '23598'
                    }
                ]
            },
            'user': {
                'id': '000200',
                'email': 'rpanes@tuxpan.com',
                'legal_name': 'FLOW S.A',
                'tax_id': '99999999-9',
                'address': 'Providencia',
                'fantasy_name': 'Pasarela de pago FLOW.'
            }
        };

        fetch('/api/v1/order/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            var popup = window.open(data.url, '_blank', 'width=600,height=400');
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    function generarUUID() {
        var d = new Date().getTime();
        if (typeof performance !== 'undefined' && typeof performance.now === 'function'){
            d += performance.now();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = (d + Math.random() * 16) % 16 | 0;
            d = Math.floor(d / 16);
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }
</script>
