<?php

function trendingTags()
{
    $trending_tags = App\Hashtag::orderBy('count', 'desc')->get();

    if (count($trending_tags) > 0) {
        if (count($trending_tags) > (int) Setting::get('min_items_page', 3)) {
            $trending_tags = $trending_tags->random((int) Setting::get('min_items_page', 3));
        }
    } else {
        $trending_tags = '';
    }

    return $trending_tags;
}

function suggestedUsers()
{
    $suggested_users = App\User::whereNotIn('id', Auth::user()->following()->get()->lists('id'))->where('id', '!=', Auth::user()->id)->get();

    if (count($suggested_users) > 0) {
        if (count($suggested_users) > (int) Setting::get('min_items_page', 3)) {
            $suggested_users = $suggested_users->random((int) Setting::get('min_items_page', 3));
        }
    } else {
        $suggested_users = '';
    }

    return $suggested_users;
}

function suggestedGroups()
{
    $suggested_groups = App\Group::whereNotIn('id', Auth::user()->groups()->pluck('group_id'))->where('type', 'open')->get();

    if (count($suggested_groups) > 0) {
        if (count($suggested_groups) > (int) Setting::get('min_items_page', 3)) {
            $suggested_groups = $suggested_groups->random((int) Setting::get('min_items_page', 3));
        }
    } else {
        $suggested_groups = '';
    }

    return $suggested_groups;
}

function suggestedPages()
{
    $suggested_pages = App\Page::whereNotIn('id', Auth::user()->pageLikes()->pluck('page_id'))->whereNotIn('id', Auth::user()->pages()->pluck('page_id'))->get();

    if (count($suggested_pages) > 0) {
        if (count($suggested_pages) > (int) Setting::get('min_items_page', 3)) {
            $suggested_pages = $suggested_pages->random((int) Setting::get('min_items_page', 3));
        }
    } else {
        $suggested_pages = '';
    }

    return $suggested_pages;
}
