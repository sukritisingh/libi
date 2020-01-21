<?php  
namespace Drupal\message_digest_admin\Form;  
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

class MessageDigestAdminPurge extends ConfigFormBase {  

    protected function getEditableConfigNames() {  
        return [  
          'message_digest_admin.adminsettings',  
        ];  
      } 
    public function getFormId() {  
        return 'tab_form';  
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('message_digest_admin.adminsettings');

    $form['sent'] = [
      '#type' => 'table',
      '#caption' => t('Staged Content'),
      '#header' => [t('Title')],
    ];

    $sent = message_digest_admin_qmessage_digest('SENT');
    $i = 0;
    while ($result = $sent->fetchObject()) {
      $nid = $result->field_node_reference_target_id;
      $link = message_digest_admin_genpath($nid, $result->title);
      $form['sent'][$i]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $link,
      ];
      $i++;
    }
    if ($i == 0) {
      $form['sent'][0]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => t('No old content'),
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('message_digest_admin.adminsettings')
      ->set('welcome_message', $form_state->getValue('welcome_message'))
      ->save();
  }  

}  
