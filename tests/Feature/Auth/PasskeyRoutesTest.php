<?php

test('guests cannot access passkey management routes', function () {
    $this->post(route('webauthn.register.options'))->assertRedirect(route('login'));
    $this->post(route('webauthn.register'))->assertRedirect(route('login'));
    $this->delete(route('webauthn.credentials.destroy', 'missing-id'))->assertRedirect(route('login'));
});
