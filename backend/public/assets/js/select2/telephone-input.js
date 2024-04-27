var input = document.querySelector("#country");
var input2 = document.querySelector("#country2");
var input3 = document.querySelector("#country3");
var errorMsg = document.querySelector("#error-msg");
var errorMsg2 = document.querySelector("#error-msg2");
var errorMsg3 = document.querySelector("#error-msg3");

const form = document.querySelector("#form");
const errorMap = ["Número Invalido", "País invalido", "Demasiado corto", "Muy Largo", "Número Invalido"];
const validMsg = document.querySelector("#valid-msg");

var iti = window.intlTelInput(input, {
  // separateDialCode:true,
  utilsScript:
    "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.0/build/js/utils.js",
});

var iti2 = window.intlTelInput(input2, {
  // separateDialCode:true,
  utilsScript:
      "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.0/build/js/utils.js",
});
var iti3 = window.intlTelInput(input3, {
  // separateDialCode:true,
  utilsScript:
      "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.0/build/js/utils.js",
});
// store the instance variable so we can access it in the console e.g. window.iti.getNumber()
window.iti = iti;
window.iti2 = iti2;
window.iti3 = iti3;

/*
const reset = () => {
  try {
    input.classList.remove("error");
    errorMsg.innerHTML = "";
    errorMsg.classList.add("hide");
    validMsg.classList.add("hide");
  }catch (error){}
};


const validateInput = (inputElement, intlTelInputInstance, errorTextMsg) => {
  reset();
  try {
    if (inputElement.value.trim()) {
      if (intlTelInputInstance.isValidNumber()) {
        validMsg.classList.remove("hide");
      } else {
        inputElement.classList.add("error");
        const errorCode = intlTelInputInstance.getValidationError();
        errorTextMsg.innerHTML = errorMap[errorCode] || 'Número Invalido';
        errorTextMsg.classList.remove("hide");
      }
    }
  }catch (error){}

};

form.onsubmit = () => {
  if (!iti.isValidNumber()) {
    validate()
    return false;
  }
};

input.addEventListener("change", () => validateInput(input, iti, errorMsg));
input.addEventListener("keyup", () => validateInput(input, iti, errorMsg));

input2.addEventListener("change", () => validateInput(input2, iti2, errorMsg2));
input2.addEventListener("keyup", () => validateInput(input2, iti2, errorMsg2));

input3.addEventListener("change", () => validateInput(input3, iti3, errorMsg3));
input3.addEventListener("keyup", () => validateInput(input3, iti3, errorMsg3));
*/