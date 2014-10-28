# Postworld : LESS Breakpoint Variables

The Postworld breakpoint variables are based on the Bootstrap screen sizes. Variables are defined to make it easy to define responsive CSS styling.

```less
    @media @tablet { .myStyles() }
```

---

The standard Bootstrap Breakpoints are defined as such:
- **lg** : 1200px
- **md** : 992px
- **sm** : 768px
- **xs** : 480px

---

## @full
**lg** : 1200px (+ and above)

## @desktop
**md** : 992px - **lg** : 1200px

## @mobile-lg
**sm** : 768px - **md** : 992px

## @mobile-md
**xs** : 480px - **sm** : 768px

## @mobile-sm
**xs** : 480px (- and below)

--- 

All the breakpoints are as follows:
```LESS
@full:                  ~"only screen and (min-width: @{screen-lg})";

@desktop-and-above:     ~"only screen and (min-width: @{screen-md})";
@desktop:               ~"only screen and (min-width: @{screen-md}) and (max-width: @{screen-lg})";
@desktop-and-below:     ~"only screen and (max-width: @{screen-lg})";

@mobile-lg-and-above:   ~"only screen and (min-width: @{screen-sm})";
@mobile-lg:             ~"only screen and (min-width: @{screen-sm}) and (max-width: @{screen-md})";
@mobile-lg-and-below:   ~"only screen and (max-width: @{screen-md})";

@mobile-md-and-above:   ~"only screen and (min-width: @{screen-xs})";
@mobile-md:             ~"only screen and (min-width: @{screen-xs}) and (max-width: @{screen-sm})";
@mobile-md-and-below:   ~"only screen and (max-width: @{screen-sm})";

@mobile-sm:             ~"only screen and (max-width: @{screen-xs})";
```
