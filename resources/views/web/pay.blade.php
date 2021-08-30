@extends('web.layout')
@section('content')


<div id="main-content">
    <div class="container clear">
        <div class="panel-body panel-body col-sm-6 col-md-6 offset-md-3 mt-5 mb-5" style="border: 1px solid #ddd;padding: 10px;background: #eee;">
           {{-- <form id="rzp-footer-form" action="{!!route('dopayment')!!}" method="POST" style="width: 100%; text-align: center" >
                @csrf

                <img class="img img-responsive" src="{{asset('images/razorpay.png')}}" />
                <br/>
                <p><br/>Price: {{$txnData['amount']}} INR </p>
                <input type="hidden" name="amount" id="amount" value="{{$txnData['amount']}}"/>
                <div class="pay">
                    <button class="razorpay-payment-button btn filled small btn-sm btn-secondary" id="paybtn" type="button">Pay with Razorpay</button>
                </div>
            </form>--}}
            <br/><br/>
            <table class="table table-responsive">
                
                <tbody>
                    <tr>
                        <td>Payment Method</td>
                        <td><img class="img img-responsive" width="200px;" src="{{asset('images/razorpay.png')}}" /></td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td>{{$txnDetail->name}}</td>
                    </tr>
                    <tr>
                        <td>Amount</td>
                        <td><strong>{{$txnData['amount']}}</strong> INR</td>
                    </tr>
                    
                </tbody>
                <tfoot>
                    <tr>
                        <td>&nbsp;</td>
                        <td><button class="razorpay-payment-button btn filled small btn-sm btn-secondary" id="paybtn" type="button">Pay with Razorpay</button></td>
                    </tr>
                </tfoot>
            </table>
            <div id="paymentDetail" style="display: none">
                <center>
                    <div>paymentID: <span id="paymentID"></span></div>
                    <div>paymentDate: <span id="paymentDate"></span></div>
                </center>
            </div>
        </div>
        

    </div>
</div>


@endsection
@section('js')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    {{--
    $('#rzp-footer-form').submit(function (e) {
        var button = $(this).find('button');
        var parent = $(this);
        button.attr('disabled', 'true').html('Please Wait...');
        $.ajax({
            method: 'get',
            url: this.action,
            data: $(this).serialize(),
            complete: function (r) {
                console.log('complete');
                console.log(r);
            }
        });
        return false;
    });
    --}}
</script>

<script>
    function padStart(str) {
        return ('0' + str).slice(-2)
    }

    function demoSuccessHandler(transaction) {
        // You can write success code here. If you want to store some data in database.
        $("#paymentDetail").removeAttr('style');
        $('#paymentID').text(transaction.razorpay_payment_id);
        var paymentDate = new Date();
        $('#paymentDate').text(
                padStart(paymentDate.getDate()) + '.' + padStart(paymentDate.getMonth() + 1) + '.' + paymentDate.getFullYear() + ' ' + padStart(paymentDate.getHours()) + ':' + padStart(paymentDate.getMinutes())
                );

        $.ajax({
            method: 'post',
            url: "{!!route('dopayment')!!}",
            data: {
                "_token": "{{ csrf_token() }}",
                "razorpay_payment_id": transaction.razorpay_payment_id,
                "razorpay_order_id": transaction.razorpay_order_id,
                "razorpay_signature": transaction.razorpay_signature,
                "txn_id": "{{$txnData['txn_id']}}"
            },
            complete: function (r) {
                console.log('complete2');
                console.log(r.responseText);
                if(r.status == '200'){
                window.location.href = "{{ route('payment_thankyou') }}";
            }else{
                window.location.href = "{{ url('profile') }}";
            }
            }
        });
    }
</script>
<script>
    var options = {
        "key": "{{ $razorpayKey }}",
        "amount": "{{$txnData['amount']*100}}",
        "name": "{{$result['commonContent']['settings']['app_name']}}",
        "currency": "INR",
        "description": 'Pay with razorpay',
        "image": "{{$result['commonContent']['settings']['website_logo']}}",
        "order_id": "{{$txnData['razorpay_order_id']}}", 
        "handler": demoSuccessHandler,
        "prefill": {
        "name": "{{$txnDetail->name}}",
        "email": "{{$txnDetail->email}}",
        "contact": "{{$txnDetail->mobile}}"
    },
    "notes": {
        "address": "$result['commonContent']['settings']['address']"
    },
    "theme": {
        "color": "#3399cc"
    }
    };
</script>
<script>
    window.r = new Razorpay(options);
    document.getElementById('paybtn').onclick = function () {
        r.open();
    };
</script>
@endsection
