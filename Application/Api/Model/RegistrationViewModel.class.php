<? php
namespace Api\ Model;
use Think\ Model\ ViewModel;
class RegistrationViewModel extends ViewModel {
    public $viewFields = array(
        'Registration' => array('id' => 'registration_id', 'order_at', 'check_at', 'hospital_id', 'date', 'doctor_id', 'status', 'source_id', 'code',
            'price', '_on' => 'User.id=Registration.user_id'),
        'User' => array('id', 'name', 'username', 'gender', 'province', 'city', 'verified_id', 'vid_type', 'credit', 'phone', 'email', 'insurance_card', 'registered_at', 'avatar'), 


        );
}