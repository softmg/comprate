Feature: Cpus

  Background:
    Given the following cpus exist:
      | id | cpu_vendor | cpu_line      | cpu_socket   | cpu_core            | cpu_frequency | cpu_l1_cache | cpu_l2_cache | cpu_l3_cache | cpu_graphic_integrate                      | cpu_virtualization_technology_support |
      | 1  | Intel      | Intel Celeron | LGA1155      | Ivy Bridge (2012)   | 2700          | 64           | 512          | 2048         | HD Graphics, 1050 МГцHD Graphics, 1050 МГц | true                                  |
      | 2  | Intel      | Intel Core i3 | LGA1151      | Sandy Bridge (2011) | 3200          | 64           | 512          | 2048         | HD Graphics, 1050 МГцHD Graphics, 1050 МГц | true                                  |


  Scenario: Get available cpu
    When I send a GET request to "/products/cpu/list" with following JSON:
    """
    {
      "page": 1,
      "pageSize": 2
    }
    """
    Then the response code should be 200
    And I should get following JSON response:
    """
    [
      {
        "cpu_vendor": "Intel",
        "cpu_line": "Intel Celeron",
        "cpu_socket": "LGA1155",
        "cpu_core": "Ivy Bridge (2012)",
        "cpu_frequency": 2700,
        "cpu_l1_cache": 64,
        "cpu_l2_cache": 512,
        "cpu_l3_cache": 2048,
        "cpu_graphic_integrate": "HD Graphics, 1050 МГцHD Graphics, 1050 МГц",
        "cpu_virtualization_technology_support": true
      },
      {
        "cpu_vendor": "Intel"
        "cpu_line": "Intel Core i3",
        "cpu_socket": "LGA1151",
        "cpu_core": "Sandy Bridge (2011)",
        "cpu_frequency": 3200,
        "cpu_l1_cache": 64,
        "cpu_l2_cache": 512,
        "cpu_l3_cache": 2048,
        "cpu_graphic_integrate": "HD Graphics, 1050 МГцHD Graphics, 1050 МГц",
        "cpu_virtualization_technology_support": true
      }
    ]
    """
