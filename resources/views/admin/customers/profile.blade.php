
       
        <div class="col-md-8">
                <!-- /.box -->
                
                <div class="box">
                    @if(!empty($customers['result']->id))
                    <div class="box-header">
                       {{-- <a href="#">
                            @if(!empty($customers['result']->avatar))
                            <img width="100px" class="img" src="{{$customers['result']->avtar}}">
                          @else
                          <img width="100px" class="img" src="https://st3.depositphotos.com/13159112/17145/v/1600/depositphotos_171453724-stock-illustration-default-avatar-profile-icon-grey.jpg">
                          @endif
                        </a>--}}
                      </div>
                    <div class="box-body">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-striped table-condensed">
                                <tbody>
                                    <tr>
                                        <td><b>Name:</b></td>
                                        <td>{{$customers['result']->name}}</td>
                                        <td><b>Email:</b></td>
                                        <td>{{$customers['result']->email}}</td>
                                        
                                        
                                    </tr>
                                  
                                    <tr>
                                       
                                        <td><b>Parent Referral id:</b></td>
                                        <td>{{$customers['result']->parent_id}}</td>
                                         <td><b>Reward Ponts:</b></td>
                                        <td>{{$customers['result']->reward_point}}</td>
                                    </tr>
                                    <tr>
                                        
                                        <td><b>Wallet balance:</b></td>
                                        <td>₹ {{$customers['result']->wallet_balance}}</td>
                                        
                                        <td><b>Kyc Status:</b></td>
                                        <td> {!!getStatusLabel($customers['result']->kyc)!!}</td>
                                    </tr>
                                    
                                </tbody>  
                            </table>
                            <hr>
                        </div>
                        
                        <div class="clearfix">&nbsp;</div>
                        @else
                        <h3 class="bg-warning">Please Select A customer</h3>
                        @endif

                    </div>
                </div>
                </div>
    
      
