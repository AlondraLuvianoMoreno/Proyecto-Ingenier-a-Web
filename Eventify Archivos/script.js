// Script for navigation bar
const bar = document.getElementById("bar");
const close = document.getElementById("close");
const nav = document.getElementById("navbar");

if (bar) {
  bar.addEventListener("click", () => {
    nav.classList.add("active");
  });
}

if (close) {
  close.addEventListener("click", () => {
    nav.classList.remove("active");
  });
}

function handleGoogleLogin(response) {
    // Token JWT de Google
    const id_token = response.credential;

    // Enviar a PHP para validar
    fetch("google-auth.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id_token=" + encodeURIComponent(id_token)
    })
    .then(res => res.text())
    .then(data => {
        if (data === "success") {
            window.location.href = "profile.php";
        } else {
            alert("Error al iniciar sesión con Google");
        }
    });
}

function openEditModal(id) {
    // Cargar datos por AJAX sin recargar la página
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "get_address.php?id=" + id, true);

    xhr.onload = function() {
        let d = JSON.parse(this.responseText);

        // Llenar los campos del formulario
        document.getElementById("edit_id").value = d.id;
        document.getElementById("calle").value = d.calle;
        document.getElementById("numero_ext").value = d.numero_ext;
        document.getElementById("numero_int").value = d.numero_int;
        document.getElementById("colonia").value = d.colonia;
        document.getElementById("cp").value = d.cp;
        document.getElementById("municipio").value = d.municipio;
        document.getElementById("ciudad").value = d.ciudad;
        document.getElementById("pais").value = d.pais;

        document.getElementById("modal-edit").style.display = "flex";
    };

    xhr.send();
}

function openAddModal() {
    document.getElementById("modal-add").style.display = "flex";
}

function closeAddModal() {
    document.getElementById("modal-add").style.display = "none";
}


function closeModal() {
    document.getElementById("modal-edit").style.display = "none";
}


// preguntar estooooo
function openEditPaymentModal(id) {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "get_payment.php?id=" + id, true);

    xhr.onload = function() {
        let d = JSON.parse(this.responseText);

        document.getElementById("edit_id_payment").value = d.id;
        document.getElementById("titular").value = d.titular;
        document.getElementById("numero_tarjeta").value = d.numero_tarjeta;
        document.getElementById("vencimiento").value = d.vencimiento;
        document.getElementById("cvv").value = d.cvv;

        document.getElementById("modal-edit-payment").style.display = "flex";
    };

    xhr.send();
}

function openAddPaymentModal() {
    document.getElementById("modal-add-payment").style.display = "flex";
}

function closeEditPaymentModal() {
    document.getElementById("modal-edit-payment").style.display = "none";
}

function closeAddPaymentModal() {
    document.getElementById("modal-add-payment").style.display = "none";
}


/* ============================
   VALIDACIÓN DE MÉTODOS DE PAGO
   ============================ */

// Reutilizable para agregar y editar
function validarTarjeta(formId) {
    // obtener valores (puede venir con espacios)
    const numField = document.querySelector(`#${formId} input[name="numero_tarjeta"]`);
    const vencField = document.querySelector(`#${formId} input[name="vencimiento"]`);
    const cvvField = document.querySelector(`#${formId} input[name="cvv"]`);

    if (!numField || !vencField || !cvvField) return true; // evita fallos si cambia estructura

    let numeroRaw = numField.value.trim();
    let venc = vencField.value.trim();
    let cvv = cvvField.value.trim();

    // eliminar no-dígitos para validar
    const numeroDigits = numeroRaw.replace(/\D/g, ""); // "1111222233334444"

    // --- Validar número: exactamente 16 números
    if (!/^\d{16}$/.test(numeroDigits)) {
        alert("El número de tarjeta debe tener exactamente 16 dígitos numéricos (puedes usar espacios cada 4 dígitos).");
        numField.focus();
        return false;
    }

    // --- Validar vencimiento formato MM/YY o MMYY (aceptamos ambos)
    // Normalizar: si escribieron "0528" -> convertir a "05/28"
    if (/^\d{4}$/.test(venc)) {
        venc = venc.substring(0,2) + "/" + venc.substring(2);
    }

    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(venc)) {
        alert("Formato de vencimiento inválido. Usa MM/YY (por ejemplo 05/28).");
        vencField.focus();
        return false;
    }

    // --- Comparar vencimiento con la fecha actual
    let parts = venc.split("/");
    let mes = parseInt(parts[0], 10);
    let anio = parseInt("20" + parts[1], 10);

    let hoy = new Date();
    let mesActual = hoy.getMonth() + 1;
    let anioActual = hoy.getFullYear();

    if (anio < anioActual || (anio === anioActual && mes < mesActual)) {
        alert("La tarjeta está vencida. Usa una fecha posterior a la actual.");
        vencField.focus();
        return false;
    }

    // --- Validar CVV (3 o 4 números)
    if (!/^\d{3,4}$/.test(cvv)) {
        alert("El CVV debe contener 3 o 4 dígitos.");
        cvvField.focus();
        return false;
    }

    // Si todo OK: normalizamos los valores para enviar al servidor
    // número sin espacios, vencimiento en MM/YY (ya lo normalizamos)
    numField.value = numeroDigits; // guardamos solo dígitos para enviar al servidor
    vencField.value = venc;
    cvvField.value = cvv.replace(/\D/g, "").substring(0,4);

    return true;
}



/* ===========================================
   VALIDACIÓN EN TIEMPO REAL PARA TARJETAS
   =========================================== */

function formatearNumeroTarjeta(input) {
    let valor = input.value.replace(/\D/g, ""); // eliminar todo lo que no sea número
    valor = valor.substring(0, 16); // máximo 16 dígitos

    // agrupar en bloques de 4
    let formateado = valor.replace(/(.{4})/g, "$1 ").trim();

    input.value = formateado;
}

function formatearVencimiento(input) {
    let valor = input.value.replace(/\D/g, ""); // solo números

    if (valor.length >= 3) {
        valor = valor.substring(0, 4); // MMYY
        input.value = valor.substring(0, 2) + "/" + valor.substring(2);
    } else {
        input.value = valor; // mientras escribe
    }
}

function validarCVVinput(input) {
    input.value = input.value.replace(/\D/g, "").substring(0, 4);
}


// APLICAR A TODOS LOS INPUTS
function activarValidacionAutomatica() {

    // NUMERO DE TARJETA
    document.querySelectorAll("#numero_tarjeta, #numero_tarjeta_add").forEach(inp => {
        inp.addEventListener("input", () => formatearNumeroTarjeta(inp));
    });

    // VENCIMIENTO MM/YY
    document.querySelectorAll("#vencimiento, #vencimiento_add").forEach(inp => {
        inp.addEventListener("input", () => formatearVencimiento(inp));
    });

    // CVV
    document.querySelectorAll("#cvv, #cvv_add").forEach(inp => {
        inp.addEventListener("input", () => validarCVVinput(inp));
    });
}

document.addEventListener("DOMContentLoaded", activarValidacionAutomatica);
