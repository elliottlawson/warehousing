<?php

it('can render the homepage')
    ->get('/')
    ->assertSee('Laravel')
    ->assertStatus(200);
