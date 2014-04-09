<?php

/**
 * @package  app
 * @extends  Controller
 */
class Controller_Recipes extends Controller_Template {

    public $fb;
    public $user;
    public $template = 'canvas.jade';
    public $data;

    public function before() {
        parent::before();
        $this->fb = \Social\Facebook::instance();
        if (!$this->fb->getUser()) {
            $params = array(
                'scope' => 'user_likes',
                'canvas' => 1,
                'fbconnect' => 0,
                'redirect_uri' => 'http://apps.facebook.com/pokaz-wiosne/',
            );
            $login_url = $this->fb->getLoginUrl($params);
            die("<script type='text/javascript'>top.location.href = '$login_url';</script>");
        } else {
            try {
                $fb_user = $this->fb->api('/me');
            } catch (Exception $e) {
                $params = array(
                    // 'scope' => 'email',
                    'canvas' => 1,
                    'fbconnect' => 0,
                    'redirect_uri' => 'http://apps.facebook.com/pokaz-wiosne/',
                );
                $login_url = $this->fb->getLoginUrl($params);
                die("<script type='text/javascript'>top.location.href = '$login_url';</script>");
            }

            $user = Model_User::find_by_fbid($fb_user['id']);
            if (!$user) {
                if (is_null($fb_user['id'])) {
                    die('<script>window.location = "//www.facebook.com/pages/Dega/172966772745531?sk=app_116262608506904";</script>');
                    Response::redirect('http://www.facebook.com/pages/Dega/172966772745531?sk=app_116262608506904');
                } else {
                    $user = Model_User::forge();
                    $user->fbid = $fb_user['id'];
                    $user->name = $fb_user['name'];
                    $user->email = isset($fb_user['email']) ? $fb_user['email'] : '';
                    $user->gender = isset($fb_user['gender']) ? $fb_user['gender'] : 'male';
                    $user->created_at = DB::expr('CURRENT_TIMESTAMP');
                    $user->save();
                }
            }
            $this->user = $user;
        }

        $this->data = array(
            'user' => $this->user,
            'access_token' => $this->fb->getAccessToken(),
            'message' => null,
        );

        // $this->data['message'] = array('type' => 'error', 'content' => 'Something went wrong.');
        if (Session::get_flash('error')) {
            $this->data['message'] = array('type' => 'error', 'content' => Session::get_flash('error'));
        } else if (Session::get_flash('success')) {
            $this->data['message'] = array('type' => 'success', 'content' => Session::get_flash('success'));
        }
    }

    public function action_index($order_by = 'recent') {
        $pagination_config = array(
            'pagination_url' => '/recipes/index/' . $order_by,
            'total_items' => Model_Recipe::find()->where('published', 1)->count(),
            'per_page' => 4,
            'uri_segment' => 4,
        );
        Pagination::set_config($pagination_config);

        $recipes = Model_Recipe::query()->related('user')->where('published', 1);
        if ($order_by == 'popular') {
            $recipes->order_by('votes_count', 'desc');
        } else {
            $recipes->order_by('created_at', 'desc');
        }
        $recipes->limit(Pagination::$per_page)->offset(Pagination::$offset);

        $this->data['recipes'] = $recipes->get();
        $this->data['ordered_by'] = $order_by;
        $this->template->content = View::forge('recipes_index.jade', $this->data);
        $this->template->content->set('pagination', Pagination::create_links(), false);
    }

    public function action_new() {
        $this->template->content = View::forge('recipes_new.jade', $this->data);
    }

    public function action_create() {
        if (Input::method() == 'POST') {
            $upload_config = array(
                'path' => DOCROOT . DS . 'uploads',
                'randomize' => true,
                'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
            );
            Upload::process($upload_config);
            if (Upload::is_valid()) {
                Upload::save();
                $uploaded_file = Upload::get_files(0);
                // resize to 370x250
                Image::load($uploaded_file['saved_to'] . $uploaded_file['saved_as'])->crop_resize(370, 250)->save_pa('m_');


                $title = trim(Input::post('title'));
                $ingredients = trim(Input::post('ingredients'));
                $description = trim(Input::post('description'));
                $email = trim(Input::post('email'));

                if ($title != '' or $ingredients != '' or $description != '' or $email != '') {
                    $recipe = Model_Recipe::forge(array(
                                'user_id' => $this->user->id,
                                'title' => Input::post('title'),
                                'ingredients' => Input::post('ingredients'),
                                'description' => Input::post('description'),
                                'photo' => Str::lower($uploaded_file['saved_as']),
                                'email' => Input::post('email'),
                                // 'created_at' => Date::time()->format('%Y-%m-%d %H-%i-%s'),
                                'created_at' => DB::expr('CURRENT_TIMESTAMP'),
                    ));
                } else {
                    $error_empty = true;
                }
            } else {
                $error_nofile = true;
            }
            if ($recipe and $recipe->save()) {
                Session::set_flash('success', 'Twoja recenzja została dodana.');
                Response::redirect('recipes/index');
            } else {
                if (isset($error_empty) and $error_empty) {
                    Session::set_flash('error', 'Wszystkie pola muszą być uzupełnione.');
                } elseif (isset($error_nofile) and $error_nofile) {
                    Session::set_flash('error', 'Recenzja musi zawierać zdjęcie.');
                } else {
                    Session::set_flash('error', 'Nie można było dodać recenzji.');
                }
                Response::redirect('recipes/new');
            }
        }
    }

    public function action_show($id = null) {
        if ($id) {
            $recipe = Model_Recipe::find_by_id($id);
            $this->data['recipe'] = $recipe;
            $this->template->content = View::forge('recipes_show.jade', $this->data);
        } else {
            Response::redirect('recipes/index');
        }
    }

    public function action_vote($recipe_id = null) {
        // if ($recipe_id) {
        //   $today = Date::time()->format('%Y-%m-%d 00:00:00');
        //   $voted = Model_Vote::find()->where('user_id', $this->user->id)->where('recipe_id', $recipe_id)
        //     ->where('created_at', '>=', $today)->count();
        //   if ($voted == 0) {
        //     $vote = Model_Vote::forge(array(
        //       'user_id' => $this->user->id,
        //       'recipe_id' => $recipe_id,
        //       'created_at' => DB::expr('CURRENT_TIMESTAMP'),
        //     ));
        //     $vote->save();
        //     $recipe = Model_Recipe::find_by_id($recipe_id);
        //     if ($recipe) {
        //       $recipe->votes_count++;
        //       $recipe->save();
        //     }
        //     Session::set_flash('success', 'Twój głos został zapisany.');
        //   } else {
        //     Session::set_flash('error', 'Dzisiaj już głosowałeś na ten przepis.');
        //   }
        // }
        Response::redirect(Input::referrer());
    }

    public function action_winners() {
        $this->template->content = View::forge('recipes_winners.jade', $this->data);
    }

}
