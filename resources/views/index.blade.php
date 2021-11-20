<!DOCTYPE html>
<html lang="ru">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="script" href="./routes/web.php">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ипотечный калькулятор</title>
</head>
<body>
<div class="container">
    <div class="row">
        <form id="calculator" @submit="checkForm">
            <div class="row mt-5">
                <div class="col-md-4 ">
                    <div class="form-group">
                        <label for="sel1">Выберите банк</label>
                        <select class="form-control" v-model="bank_id" @change="getMortgage()" id="bank">
                            <option v-for="bank in banks" :value="bank.id">@{{bank.name}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-1">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="exampleFormControlSelect2">Выберите ипотеку</label>
                        <select class="form-control" v-model="mortgage_id" @change="getPercent()" id="mortgage">
                            <option v-for="mortgage in mortgages" :value="mortgage.id">@{{mortgage.name}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-1">
                <div class="col-md-4 ">
                    <div class="form-group">
                        <label for="formGroupExampleInput">Стоимость недвижимости</label>
                        <input type="number" max="100000000" min="0" class="form-control" v-model="price" id="price"
                               placeholder="Введите стоимость недвижимости . руб">
                    </div>
                </div>
                <div class="col-md-4" style="margin-left: 50px">
                    <div class="view-result">
                        <p v-if="creditAmount"> Сумма кредита: @{{ creditAmount }} руб </p>
                        <p v-if="resultPercent"> Процентная ставка: @{{ resultPercent }} % </p>
                    </div>
                </div>
            </div>
            <div class="row mt-1">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="formGroupExampleInput">Первоначальный взнос </label>
                        <input type="number" max="100000000" min="0" class="form-control" v-model="anInitialFee"
                               id="anInitialFee" placeholder="0 руб.">
                    </div>
                </div>
                <div class="col-md-4" style="margin-left: 50px">
                    <div class="view-result">
                        <p v-if="monthlyPayment"> Ежемесячный платеж: @{{ monthlyPayment }} руб</p>
                        <p v-if="overpayment"> Переплата по ипотеке: @{{ overpayment }} руб</p>
                    </div>
                </div>
            </div>
            <div class="row mt-1">
                <div class="col-md-3 ">
                    <div class="form-group">
                        <label for="exampleFormControlSelect">Выберите срок ипотеки</label>
                        <input type="number" min="12" max="360" step="12" v-model="mortgageTerm" class="form-control"
                               id="mortgageTerm">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="">Ставка, %</label>
                        <input type="text" maxlength="11" minlength="9" v-if="bank_id !==-1" disabled v-model="percent"
                               id="percent" class="form-control">
                        <input type="number" max="100" min="0" v-else v-model="percent" id="percent"
                               class="form-control">
                    </div>
                </div>
            </div>
            <div class="vue-errors">
                <p class="vue-error-message" v-if="errors.length">
                    <b>Пожалуйста исправьте ошибки:</b>
                <ul>
                    <li v-for="err in errors">@{{ err }}</li>
                </ul>
                </p>
            </div>
            <div class="php-errors">
                <p class="php-errors-message" v-if="validData"> Данные не валидны </p>
            </div>
            <div class="row mt-10">
                <div class="col-md-2" style="margin-top: 15px;margin-left: 300px">
                    <p>
                        <button class="btn btn-primary submit-button" type="button" v-on:click="onSubmit"> Расчитать
                        </button>
                    </p>
                    <p>
                        <button class="btn btn-primary submit-button" v-if="hidden" v-on:click="getPdf" type="button">
                            Скачать Pdf
                        </button>
                    </p>
                    <p>
                        <button class="btn btn-primary submit-button" v-if="hidden" v-on:click="getXlsx" type="button">
                            Скачать xlsx
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#calculator',
        data: {
            errors: [],
            bank_id: -1,
            banks: [],
            mortgage_id: null,
            mortgages: [],
            percent: null,
            price: 140000,
            anInitialFee: 1000,
            mortgageTerm: 12,
            overpayment: null,
            creditAmount: null,
            monthlyPayment: null,
            resultPercent: null,
            hidden: false,
            randomString: Math.random().toString(36).substr(2, 7),
            validData: false
        },
        created() {
            axios.get('/calculator/getBanks', {})
                .then((response) => {
                    this.banks = response.data;
                    console.log(this.banks);
                });
        },
        methods: {
            getMortgage: function () {
                axios.post('/calculator/getMortgage', {
                    bank_id: this.bank_id
                })
                    .then((response) => {
                        this.mortgages = response.data;
                        console.log(this.mortgages);
                    });
            },
            getPercent: function () {
                axios.post('/calculator/getPercent', {
                    mortgage_id: this.mortgage_id
                })
                    .then((response) => {
                        this.percent = response.data.percent;
                        console.log(this.percent);
                    });
            },
            checkForm: function (e) {
                this.errors = [];
                if (!this.price) {
                    this.errors.push('Укажите корректную стоимость недвижимости');
                }
                if (!this.percent) {
                    this.errors.push('Укажите корректную процентную ставку');
                }
                if (!this.anInitialFee) {
                    this.errors.push('Укажите корректный первоначальный взнос')
                }
                if (this.anInitialFee <= 0) {
                    this.errors.push('Первоначальный взнос должен быть положительным числом')
                }if (this.percent > 100) {
                    this.errors.push('Процентная ставка не может быть больше 100%')
                }
                if (this.percent <= 0) {
                    this.errors.push('Процентная ставка не может быть отрицательной или нулевой')
                }
                if (Number(this.price) <= Number(this.anInitialFee)) {
                    this.errors.push('Первоначальный взнос должен быть меньше стоимости недвижимости')
                }
                if (this.mortgageTerm % 12 !== 0) {
                    this.errors.push('Введите корректный срок ипотеки')
                }
                if (!this.errors.length) {
                    return true;
                }
                e.preventDefault();
                return false;
            },
            onSubmit: function (e) {
                if (this.checkForm(e)) {
                    axios.post('/calculator/results', {
                        price: this.price,
                        anInitialFee: this.anInitialFee,
                        percent: this.percent,
                        mortgageTerm: this.mortgageTerm,
                        randomString: this.randomString,
                    })
                        .then((response) => {
                            this.validData = response.data.isValid;
                            if (this.validData !== 'err') {
                                console.log(response.data);
                                this.overpayment = response.data.overpayment;
                                this.creditAmount = response.data.creditAmount;
                                this.resultPercent = response.data.percent;
                                this.monthlyPayment = response.data.monthlyPayment;
                                this.hidden = true
                            }
                        })
                }
            },
            getPdf() {
                axios({
                    url: '/calculator/getPdf',
                    method: 'POST',
                    responseType: 'arraybuffer',
                    data: {
                        randomString: this.randomString
                    }
                }).then(response => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');

                    link.href = url;
                    link.setAttribute('download', 'План-график.pdf');
                    document.body.appendChild(link);
                    link.click();
                });
            },
            getXlsx() {
                axios({
                    url: '/calculator/getXlsx',
                    method: 'POST',
                    responseType: 'arraybuffer',
                    data: {
                        randomString: this.randomString
                    }
                }).then(response => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');

                    link.href = url;
                    link.setAttribute('download', 'План-график.xlsx');
                    document.body.appendChild(link);
                    link.click();
                });
            }

        }
    })
</script>
</body>
</html>
